<?php 


// // 校验数据当前访问用户的箱子 箱子（session）有没有登录的登陆标识
// session_start();
// if (empty($_SESSION['current_login_user'])) {
//   // 意味着没有登录
//   header('Location: /admin/login.php');
// }

require_once '../functions.php';
// 判断用户是否登录一定最先去做
bx_get_current_user();

// 获取页面所需要的数据
// 重复的操作一定封装起来
$posts_count = bx_fetch_one('SELECT count(1) as num FROM posts;')['num'];
$posts_drafted_count = bx_fetch_one("SELECT count(1) as num FROM posts WHERE status = 'drafted';")['num'];
$categories_count = bx_fetch_one('SELECT count(1) as num FROM categories;')['num'];
$comments_count = bx_fetch_one('SELECT count(1) as num FROM comments;')['num'];
$comments_held_count = bx_fetch_one("SELECT count(1) as num FROM comments WHERE status = 'held';")['num'];

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Dashboard &laquo; Admin</title>
  <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="/static/assets/vendors/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" href="/static/assets/vendors/nprogress/nprogress.css">
  <link rel="stylesheet" href="/static/assets/css/admin.css">
  <script src="/static/assets/vendors/nprogress/nprogress.js"></script>
</head>
<body>
  <script>NProgress.start()</script>

  <div class="main">
    <?php include 'inc/navbar.php'; ?>
    <div class="container-fluid">
      <div class="jumbotron text-center">
        <h1>One Belt, One Road</h1>
        <p>Thoughts, stories and ideas.</p>
        <p><a class="btn btn-primary btn-lg" href="post-add.html" role="button">写文章</a></p>
      </div>
      <div class="row">
        <div class="col-md-4">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">站点内容统计：</h3>
            </div>
            <ul class="list-group">
              <li class="list-group-item"><strong><?php echo $posts_count ?></strong>篇文章（<strong><?php echo $posts_drafted_count ?></strong>篇草稿）</li>
              <li class="list-group-item"><strong><?php echo $categories_count ?></strong>个分类</li>
              <li class="list-group-item"><strong><?php echo $comments_count ?></strong>条评论（<strong><?php echo $comments_held_count ?></strong>条待审核）</li>
            </ul>
          </div>
        </div>
        <div class="col-md-4">
          <canvas id="chart"></canvas>
        </div>
        <div class="col-md-4"></div>
      </div>
    </div>
  </div>
  <?php $current_page = 'index'; ?>
  <?php include 'inc/sidebar.php'; ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script src="/static/assets/vendors/chart/Chart.js"></script>
  <script>
    var ctx = document.getElementById('chart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'pie',
        data: {
            datasets: [{
                data: [<?php echo $posts_count ?>, <?php echo $categories_count ?>, <?php echo $comments_count ?>],
                backgroundColor: [
                  'hotpink',
                  'pink',
                  'deeppink'
                ]
            }],
            labels: [
              '文章',
              '分类',
              '评论'
            ]
        }
    });
  </script>
  <script>NProgress.done()</script>
</body>
</html>
