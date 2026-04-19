<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Không tìm thấy trang</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: #333;
        }

        .container {
            text-align: center;
            padding: 20px;
        }

        .error-code {
            font-size: 150px;
            font-weight: 900;
            color: #3498db;
            text-shadow: 4px 4px 0px #fff, 7px 7px 0px rgba(0,0,0,0.05);
            line-height: 1;
            margin-bottom: 10px;
        }

        .error-message {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .description {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 30px;
            max-width: 400px;
            line-height: 1.6;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 30px;
            background-color: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-home:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        .icon-404 {
            font-size: 80px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="icon-404">
            <i class="fas fa-map-signs"></i>
        </div>
        <h1 class="error-code">404</h1>
        <p class="error-message">Ối! Trang này không tồn tại.</p>
        <p class="description">
            Đường dẫn bạn vừa truy cập có thể đã bị xóa hoặc không còn tồn tại trên hệ thống RoomFinder.
        </p>
        
        <a href="/" class="btn-home">
            <i class="fas fa-arrow-left"></i>
            Quay lại Trang Chủ
        </a>
    </div>

</body>
</html>