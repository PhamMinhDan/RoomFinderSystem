const LocationService = (() => {
  const BASE = "https://provinces.open-api.vn/api/v1";
  const _cache = {};

  async function _get(url) {
    if (_cache[url]) return _cache[url];
    const res = await fetch(url);
    if (!res.ok)
      throw new Error(`LocationService: HTTP ${res.status} – ${url}`);
    const data = await res.json();
    _cache[url] = data;
    return data;
  }

  return {
    /** Lấy tất cả tỉnh/thành */
    getCities: () => _get(`${BASE}/p/`),

    /** Lấy quận/huyện theo mã tỉnh */
    getDistricts: (cityCode) => _get(`${BASE}/p/${cityCode}?depth=2`),

    /** Lấy phường/xã theo mã quận */
    getWards: (districtCode) => _get(`${BASE}/d/${districtCode}?depth=2`),
  };
})();

function LocationDropdown(cfg) {
  let _items = [];
  let _open = false;

  const { triggerEl, listEl, searchEl, dropdownEl, onSelect } = cfg;

  function _render(items) {
    _items = items;
    _fill(_items);
  }

  function _fill(items) {
    listEl.innerHTML = "";
    if (!items.length) {
      listEl.innerHTML =
        '<div class="loc-option loc-empty">Không có dữ liệu</div>';
      return;
    }
    items.forEach((item) => {
      const div = document.createElement("div");
      div.className = "loc-option";
      div.textContent = item.name;
      div.dataset.code = item.code;
      div.addEventListener("click", () => {
        onSelect(item);
        close();
      });
      listEl.appendChild(div);
    });
  }

  function open(items) {
    if (items) _render(items);
    dropdownEl.style.display = "block";
    _open = true;
    if (searchEl) {
      searchEl.value = "";
      searchEl.focus();
    }
  }

  function close() {
    dropdownEl.style.display = "none";
    _open = false;
  }

  function toggle(items) {
    _open ? close() : open(items);
  }

  function setLoading() {
    listEl.innerHTML =
      '<div class="loc-option loc-loading"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';
    dropdownEl.style.display = "block";
    _open = true;
  }

  function disable() {
    triggerEl.style.opacity = "0.45";
    triggerEl.style.pointerEvents = "none";
  }

  function enable() {
    triggerEl.style.opacity = "1";
    triggerEl.style.pointerEvents = "auto";
  }

  function setLabel(text, placeholder) {
    const span = triggerEl.querySelector("span") || triggerEl;
    span.textContent = text;
    if (placeholder) span.style.color = "var(--gray-400)";
    else span.style.color = "var(--gray-800, #1f2937)";
  }

  // Search filter
  if (searchEl) {
    searchEl.addEventListener("input", () => {
      const q = searchEl.value.toLowerCase().trim();
      _fill(
        q ? _items.filter((i) => i.name.toLowerCase().includes(q)) : _items,
      );
    });
  }

  // Click bên ngoài → đóng
  document.addEventListener("click", (e) => {
    if (
      _open &&
      !dropdownEl.contains(e.target) &&
      !triggerEl.contains(e.target)
    ) {
      close();
    }
  });

  return {
    open,
    close,
    toggle,
    setLoading,
    disable,
    enable,
    setLabel,
    render: _render,
  };
}

function LocationPicker(cfg) {
  const state = { city: null, district: null, ward: null };

  const cityDD = LocationDropdown({
    triggerEl: cfg.cityTrigger,
    listEl: cfg.cityList,
    searchEl: cfg.citySearch,
    dropdownEl: cfg.cityDropdown,
    onSelect: async (city) => {
      state.city = city;
      state.district = null;
      state.ward = null;
      cityDD.setLabel(city.name);

      districtDD.setLabel("Quận, huyện *", true);
      wardDD.setLabel("Phường, xã *", true);
      districtDD.disable();
      wardDD.disable();

      districtDD.setLoading();
      districtDD.enable();
      try {
        const data = await LocationService.getDistricts(city.code);
        districtDD.render(data.districts || []);
      } catch {
        districtDD.render([]);
      }

      cfg.onChange && cfg.onChange({ ...state });
    },
  });

  const districtDD = LocationDropdown({
    triggerEl: cfg.districtTrigger,
    listEl: cfg.districtList,
    searchEl: cfg.districtSearch,
    dropdownEl: cfg.districtDropdown,
    onSelect: async (district) => {
      state.district = district;
      state.ward = null;
      districtDD.setLabel(district.name);

      wardDD.setLabel("Phường, xã *", true);
      wardDD.disable();

      wardDD.setLoading();
      wardDD.enable();
      try {
        const data = await LocationService.getWards(district.code);
        wardDD.render(data.wards || []);
      } catch {
        wardDD.render([]);
      }

      cfg.onChange && cfg.onChange({ ...state });
    },
  });

  const wardDD = LocationDropdown({
    triggerEl: cfg.wardTrigger,
    listEl: cfg.wardList,
    searchEl: cfg.wardSearch,
    dropdownEl: cfg.wardDropdown,
    onSelect: (ward) => {
      state.ward = ward;
      wardDD.setLabel(ward.name);
      cfg.onChange && cfg.onChange({ ...state });
    },
  });

  // Khởi tạo
  districtDD.disable();
  wardDD.disable();

  // Gắn trigger click
  cfg.cityTrigger.addEventListener("click", async () => {
    if (cityDD._open) {
      cityDD.close();
      return;
    }
    cityDD.setLoading();
    try {
      const cities = await LocationService.getCities();
      cityDD.open(cities);
    } catch {
      cityDD.open([]);
    }
  });

  cfg.districtTrigger.addEventListener("click", () => {
    if (!state.city) return;
    districtDD.toggle();
  });

  cfg.wardTrigger.addEventListener("click", () => {
    if (!state.district) return;
    wardDD.toggle();
  });

  return {
    getState: () => ({ ...state }),
    reset: () => {
      state.city = null;
      state.district = null;
      state.ward = null;
      cityDD.setLabel("Tỉnh, thành phố *", true);
      districtDD.setLabel("Quận, huyện *", true);
      wardDD.setLabel("Phường, xã *", true);
      districtDD.disable();
      wardDD.disable();
    },
  };
}

async function initCitySelect(selectEl, onChange) {
  if (!selectEl) return;
  try {
    const cities = await LocationService.getCities();

    // Nếu là <select> thông thường
    if (selectEl.tagName === "SELECT") {
      // Giữ option đầu (placeholder)
      const placeholder = selectEl.options[0];
      selectEl.innerHTML = "";
      selectEl.appendChild(placeholder);

      cities.forEach((c) => {
        const opt = document.createElement("option");
        opt.value = c.code;
        opt.textContent = c.name;
        selectEl.appendChild(opt);
      });

      selectEl.addEventListener("change", () => {
        const code = selectEl.value;
        const name = selectEl.options[selectEl.selectedIndex].text;
        onChange && onChange(name, code);
      });
    }
  } catch (e) {
    console.warn("initCitySelect error:", e);
  }
}
