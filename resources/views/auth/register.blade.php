<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Hệ thống Quản lý Lớp học</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 450px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        .register-container::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        .register-header {
            text-align: center;
            margin-bottom: 35px;
        }
        .register-header h1 { color: #333; font-size: 28px; margin-bottom: 8px; font-weight: 700; }
        .register-header p { color: #666; font-size: 16px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #555; font-weight: 600; font-size: 14px; }
        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 8px 16px rgba(102,126,234,0.1);
        }
        .btn-register {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        .btn-register:hover { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(102,126,234,0.3); }
        .login-link { text-align: center; font-size: 14px; color: #666; }
        .login-link a { color: #667eea; text-decoration: none; font-weight: 600; }
        .login-link a:hover { color: #764ba2; text-decoration: underline; }
        .error-message { background: #f8d7da; color: #721c24; padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; border: 1px solid #f5c6cb; font-size: 14px; }
        .field-error { color: #dc3545; font-size: 12px; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>📝 Đăng ký</h1>
            <p>Tạo tài khoản mới</p>
        </div>

        @if ($errors->any())
        <div class="error-message">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
    <label for="name">Họ và tên</label>
    <input type="text" id="name" name="name"
        value="{{ old('name') }}"
        placeholder="Nhập họ và tên đầy đủ"
        required autofocus>
    @error('name')<div class="field-error">{{ $message }}</div>@enderror
</div>

            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username"
                    value="{{ old('username') }}"
                    placeholder="Nhập tên đăng nhập"
                    required>
                @error('username')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                    value="{{ old('email') }}"
                    placeholder="Nhập địa chỉ email"
                    required>
                @error('email')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password"
                    placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)"
                    required>
                @error('password')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Xác nhận mật khẩu</label>
                <input type="password" id="password_confirmation"
                    name="password_confirmation"
                    placeholder="Nhập lại mật khẩu"
                    required>
            </div>

            <button type="submit" class="btn-register">Tạo tài khoản</button>
        </form>

        <div class="login-link">
            Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập ngay</a>
        </div>
    </div>
</body>
</html>