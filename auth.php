<?php
session_start();

// Если пользователь уже авторизован, показываем приветствие
if (isset($_SESSION['user'])) {
    $fio = isset($_SESSION['user']['fio_short']) ? htmlspecialchars($_SESSION['user']['fio_short']) : '';
    echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>Добро пожаловать</title>';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<style>body{font-family:Segoe UI,Arial,sans-serif;background:#f7f7f7;} .auth-container{max-width:370px;margin:80px auto;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(60,60,120,0.18);padding:38px 32px 32px 32px;position:relative;box-sizing:border-box;text-align:center;} .user-fio{font-size:22px;font-weight:600;color:#2d3a5a;margin:18px 0;} .go-btn{margin-top:24px;padding:12px 32px;font-size:18px;border-radius:7px;border:none;background:linear-gradient(90deg,#6c63ff 0%,#5a5ae6 100%);color:#fff;font-weight:600;cursor:pointer;box-shadow:0 2px 8px #e0e7ff;transition:background 0.2s;} .go-btn:hover{background:linear-gradient(90deg,#5a5ae6 0%,#6c63ff 100%);} </style>';
    echo '</head><body><div class="auth-container">';
    echo '<div style="font-size:40px;margin-bottom:10px;">👨‍⚕️</div>';
    echo '<div class="user-fio">Здравствуйте, '.$fio.'!</div>';
    echo '<a href="patient_prescription.php" class="go-btn">Перейти к работе</a>';
    echo '</div></body></html>';
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['empl_code'] ?? '');
    $pwd = trim($_POST['empl_pwd'] ?? '');
    if ($code && $pwd) {
        $conn = pg_connect("host=localhost dbname=Legacy user=postgres password=password");
        if ($conn) {
            $res = pg_query_params($conn, 'SELECT * FROM public.m_employes WHERE empl_code = $1 AND empl_pwd = $2', [$code, $pwd]);
            $user = pg_fetch_assoc($res);
            if ($user) {
                // Формируем Фамилия И.О.
                $fio_short = $user['lst_name'];
                if (!empty($user['fst_name'])) {
                    $fio_short .= ' ' . mb_substr($user['fst_name'], 0, 1, 'UTF-8') . '.';
                }
                if (!empty($user['ptr_name'])) {
                    $fio_short .= mb_substr($user['ptr_name'], 0, 1, 'UTF-8') . '.';
                }
                $_SESSION['user'] = [
                    'empl_id' => $user['empl_id'],
                    'empl_code' => $user['empl_code'],
                    'empl_name' => $user['empl_name'],
                    'fio_short' => $fio_short
                ];
                header('Location: patient_prescription.php');
                exit;
            } else {
                $error = 'Неверный логин или пароль';
            }
            pg_close($conn);
        } else {
            $error = 'Ошибка подключения к базе данных';
        }
    } else {
        $error = 'Введите логин и пароль';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Авторизация</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(120deg, #e0e7ff 0%, #f7f7f7 100%);
            min-height: 100vh;
            margin: 0;
        }
        .auth-container {
            max-width: 370px;
            margin: 80px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(60,60,120,0.18);
            padding: 38px 32px 32px 32px;
            position: relative;
            transition: box-shadow 0.3s;
            box-sizing: border-box;
        }
        .auth-container:hover {
            box-shadow: 0 12px 40px rgba(60,60,120,0.22);
        }
        h2 {
            margin-top: 0;
            margin-bottom: 24px;
            text-align: center;
            font-weight: 600;
            color: #2d3a5a;
            letter-spacing: 1px;
        }
        label {
            display: block;
            margin-top: 18px;
            font-weight: 500;
            color: #2d3a5a;
        }
        .input-wrap {
            position: relative;
        }
        .input-wrap .icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #b0b8d1;
            font-size: 17px;
            pointer-events: none;
        }
        input[type=text], input[type=password], button {
            width: 100%;
            box-sizing: border-box;
            font-size: 17px;
            padding: 10px 10px 10px 36px;
            margin-top: 6px;
            border-radius: 7px;
            border: 1px solid #cfd8dc;
            background: #f8fafc;
            transition: border 0.2s;
        }
        input[type=text]:focus, input[type=password]:focus {
            border: 1.5px solid #6c63ff;
            background: #fff;
            outline: none;
        }
        button {
            margin-top: 28px;
            padding: 12px;
            font-size: 18px;
            border-radius: 7px;
            border: none;
            background: linear-gradient(90deg, #6c63ff 0%, #5a5ae6 100%);
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px #e0e7ff;
            transition: background 0.2s;
        }
        button:hover {
            background: linear-gradient(90deg, #5a5ae6 0%, #6c63ff 100%);
        }
        .error {
            color: #b00;
            margin-top: 18px;
            text-align: center;
            font-size: 16px;
        }
        @media (max-width: 500px) {
            .auth-container {
                margin: 30px 8px;
                padding: 22px 8px 18px 8px;
            }
            h2 { font-size: 22px; }
        }
    </style>
</head>
<body>
<div class="auth-container">
    <h2><span class="icon" style="font-size:22px;vertical-align:middle;">&#128274;</span> Вход в систему</h2>
    <?php if ($error): ?>
        <div class="error"><span class="icon" style="color:#b00;font-size:16px;">&#9888;</span> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <label for="empl_code">Логин</label>
        <div class="input-wrap">
            <span class="icon">&#128100;</span>
            <input type="text" id="empl_code" name="empl_code" required autofocus placeholder="Введите логин">
        </div>
        <label for="empl_pwd">Пароль</label>
        <div class="input-wrap">
            <span class="icon">&#128273;</span>
            <input type="password" id="empl_pwd" name="empl_pwd" required placeholder="Введите пароль">
        </div>
        <button type="submit"><span class="icon" style="font-size:17px;vertical-align:middle;">&#10148;</span> Войти</button>
    </form>
</div>
</body>
</html> 
