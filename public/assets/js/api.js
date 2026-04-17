/**
 * API Utility - Xử lý tất cả API calls
 */

const API = {
  // Base URL
  baseUrl: "/api",

  /**
   * Upload ảnh
   * @param {File} file
   * @returns {Promise<{success: boolean, url: string, publicId: string, error: string|null}>}
   */
  async uploadImage(file) {
    return this._uploadFile(file, "image");
  },

  /**
   * Upload video
   * @param {File} file
   * @returns {Promise<{success: boolean, url: string, publicId: string, error: string|null}>}
   */
  async uploadVideo(file) {
    return this._uploadFile(file, "video");
  },

  /**
   * Upload file (internal)
   */
  async _uploadFile(file, type) {
    try {
      const formData = new FormData();
      formData.append("file", file);
      formData.append("type", type);

      const response = await fetch(`${this.baseUrl}/upload`, {
        method: "POST",
        body: formData,
      });

      if (!response.ok) {
        const data = await response.json();
        return {
          success: false,
          error: data.error || "Upload failed",
          url: null,
          publicId: null,
        };
      }

      return await response.json();
    } catch (error) {
      return {
        success: false,
        error: error.message || "Network error",
        url: null,
        publicId: null,
      };
    }
  },

  /**
   * Delete file from Cloudinary
   */
  async deleteFile(publicId) {
    try {
      const response = await fetch(`${this.baseUrl}/delete`, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `publicId=${encodeURIComponent(publicId)}`,
      });

      if (!response.ok) {
        const data = await response.json();
        return { success: false, error: data.error };
      }

      return await response.json();
    } catch (error) {
      return {
        success: false,
        error: error.message || "Network error",
      };
    }
  },

  /**
   * Update listing
   */
  async updateListing(id, data) {
    try {
      const response = await fetch(`/landlord/listings/${id}/update`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      });

      if (!response.ok) {
        const data = await response.json();
        return { success: false, error: data.error };
      }

      return await response.json();
    } catch (error) {
      return {
        success: false,
        error: error.message || "Network error",
      };
    }
  },

  /**
   * Confirm appointment
   */
  async confirmAppointment(id) {
    try {
      const response = await fetch(`/landlord/appointments/${id}/confirm`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
      });

      if (!response.ok) {
        const data = await response.json();
        return { success: false, error: data.error };
      }

      return await response.json();
    } catch (error) {
      return {
        success: false,
        error: error.message || "Network error",
      };
    }
  },

  /**
   * Cancel appointment
   */
  async cancelAppointment(id) {
    try {
      const response = await fetch(`/landlord/appointments/${id}/cancel`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
      });

      if (!response.ok) {
        const data = await response.json();
        return { success: false, error: data.error };
      }

      return await response.json();
    } catch (error) {
      return {
        success: false,
        error: error.message || "Network error",
      };
    }
  },

  /**
   * Get listing details
   */
  async getListing(id) {
    try {
      const response = await fetch(`/api/listings/${id}`);

      if (!response.ok) {
        const data = await response.json();
        return { success: false, error: data.error };
      }

      return await response.json();
    } catch (error) {
      return {
        success: false,
        error: error.message || "Network error",
      };
    }
  },
};

// Export for use in modules (if using ES6 modules)
if (typeof module !== "undefined" && module.exports) {
  module.exports = API;
}
