<?php 
//载入配置文件
require_once '../config.php';

// 给用户找一个箱子如果之前有就用，没有就找一个新的
session_start();

function login() {
  // 1.接收并校验
  // 2.持久化
  // 3.响应

  if (empty($_POST['email'])) {
    $GLOBALS['message'] = '请填写邮箱';
    return;
  }
  if (empty($_POST['password'])) {
    $GLOBALS['message'] = '请填写密码';
    return;
  }


  $email = $_POST['email'];
  $password = $_POST['password'];

  // 当客户端提交过来完整的表单信息就应该开始对其进行数据校验
  $conn = mysqli_connect(BX_DB_HOST, BX_DB_USER, BX_DB_PASS, BX_DB_NAME);
  if (!$conn) {
    exit('<h1>连接数据库失败</h1>');
  }

  $query = mysqli_query($conn, "SELECT * FROM users WHERE email = '{$email}' LIMIT 1;");

  if (!$query) {
    $GLOBALS['message'] = 'Login failed, please try again!';
    return;
  }

  // 获取登录用户
  $user = mysqli_fetch_assoc($query);

  if (!$user) {
    // 用户名不存在
    $GLOBALS['message'] = '邮箱与密码不匹配';
    return;
  }

  // md5 已经不安全了
  if ($user['password'] !== md5($password)) {
    $GLOBALS['message'] = '邮箱与密码不匹配';
    return;
  }

  //存一个登录标识
  $_SESSION['current_login_user'] = $user;

  // 一切ok 可以跳转
  header('Location: /admin/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  login();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'logout') {
  // 删除了登录标识
  unset($_SESSION['current_login_user']);
}
 ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Sign in &laquo; Admin</title>
  <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="/static/assets/css/admin.css">
  <link rel="stylesheet" href="/static/assets/vendors/animate/animate.css">
</head>
<body>
  <div class="login">
    <form class="login-wrap<?php echo isset($message) ? ' shake animated' : '' ?>" action="<?php echo $_SERVER['PHP_SELF']; ?>" method = "post" novalidate autocomplete = "off">
      <img class="avatar" src="/static/assets/img/default.png">
      <!-- 有错误信息时展示 -->
      <?php if (isset($message)): ?>
        <div class="alert alert-danger">
        <strong>错误！</strong> <?php echo $message ?>
      </div>
      <?php endif ?>
      <div class="form-group">
        <label for="email" class="sr-only">邮箱</label>
        <input id="email" name="email" type="email" class="form-control" placeholder="邮箱" autofocus value="<?php echo empty($_POST['email']) ? '' : $_POST['email']; ?>">
      </div>
      <div class="form-group">
        <label for="password" class="sr-only">密码</label>
        <input id="password" name="password" type="password" class="form-control" placeholder="密码">
      </div>
      <button class="btn btn-primary btn-block" >登 录</button>
    </form>
  </div>
  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script>
    $(function ($) {
      // 目标：在用户输入自己的邮箱后，页面上展示这个邮箱对应的头像

      var emailFormat = /^[a-zA-Z0-9]+@[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/
      $('#email').on('blur', function () {
        var value = $(this).val()
        // 忽略掉文本框为空或者不是一个邮箱
        if (!value || !emailFormat.test(value)) return

        // 用户输入了一个合理的邮箱
        // 因为客户端的 js 无法直接操作数据库，应该通过 js 发送AJAX请求告诉服务端的某个接口，
        // 让这个接口帮助客户端获取头像地址

        $.get('/admin/api/avatar.php', {email: value}, function (res) {
          // 希望res => 这个邮箱对应的头像地址

          if (!res) return
          // 展示上面的 img 元素上
          $('.avatar').attr('src', res)
        })
      })
    })
  </script>
</body>
</html>
