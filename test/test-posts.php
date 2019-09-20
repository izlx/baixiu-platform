<?php 

require_once '../functions.php';

bx_get_current_user();

// 处理分页参数
$size = 20;
$page = empty($_GET['page']) ? 1 : (int)$_GET['page'];

// $page = $page < 1 ? 1 : $page;
if ($page < 1) {
  // 跳转到第一页
  header('Location: /admin/posts.php?page=1');
}

// 只要是处理分页功能一定会用到最大的页码数
// $total_pages = ceil($total_count / $size)
$total_count = (int)bx_fetch_one('SELECT count(1) as count from posts;')['count'];
$total_pages = (int)ceil($total_count / $size);

// $page = $page > $total_pages ? $total_pages : $page;
if ($page > $total_pages) {
  // 跳转到最后一页
  header('Location: /admin/posts.php?page=' . $total_pages);
}

// 接收筛选参数
// =========================================

$where = '1 = 1';
$search = '';
// 分类的筛选
if (!empty($_GET['category'] && $_GET['category'] !== 'all')) {
  $where .= ' and posts.category_id = ' . $_GET['category'];
  $search .= '&category=' . $_GET['category'];
}
// 状态的筛选
if (!empty($_GET['status'] && $_GET['status'] !== 'all')) {
  $where .= " and posts.status = '{$_GET['status']}'";
  $search .= '&status=' . $_GET['status'];
}

// where => "1 = 1 and posts.category_id = 1 and posts.status = 'published'"
// search => "&category=1&status='published'"

// 计算越过多少条(偏移量)
$offset = ($page - 1) * $size;


// 获取全部数据
$posts = bx_fetch_all("SELECT
  posts.id,
  posts.title,
  users.nickname as user_name,
  categories.name as category_name,
  posts.created,
  posts.status
FROM posts
INNER JOIN categories on posts.category_id = categories.id
INNER JOIN users on posts.user_id = users.id
WHERE {$where}
ORDER BY posts.created DESC
LIMIT {$page}, {$size};");

// 查询全部分类信息
$categories = bx_fetch_all('select * from categories;');

// 处理分页页码


$visiables = 5;
// 计算最大和最小展示的页码
$begin = $page - ($visiables - 1) / 2;
$end = $begin + $visiables - 1;

// 重点考虑合理性
// $begin > 0  end<= total_pages
$begin = $begin < 1 ? 1 : $begin; // 确保了begin不会小于1
$end = $begin + $visiables - 1;// 同步二者关系
$end = $end > $total_pages ? $total_pages : $end; // 确保end不会大于total_pages
$begin = $end - ($visiables - 1); // 同步
$begin = $begin < 1 ? 1 : $begin; // 确保不能小于1


/**
 * 转换状态显示
 * @param  string $status 英文状态
 * @return string         中文状态
 */
function convert_status ($status) {
  $dict = array(
    'published' => '已发布',
    'drafted' => '草稿',
    'trashed' => '回收站',
  );

  return isset($dict[$status]) ? $dict[$status] : '未知';
}

/**
 * 转换时间格式
 * @param  [type] $created [description]
 * @return [type]          [description]
 */
function convert_created ($created) {
  $timestamp = strtotime($created);
  return date('Y年m月d日<b\r>H:i:s', $timestamp);
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Posts &laquo; Admin</title>
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
      <div class="page-title">
        <h1>所有文章</h1>
        <a href="post-add.html" class="btn btn-primary btn-xs">写文章</a>
      </div>
      <!-- 有错误信息时展示 -->
      <!-- <div class="alert alert-danger">
        <strong>错误！</strong>发生XXX错误
      </div> -->
      <div class="page-action">
        <!-- show when multiple checked -->
        <a class="btn btn-danger btn-sm" href="javascript:;" style="display: none">批量删除</a>
        <form class="form-inline" action="<?php echo $_SERVER['PHP_SELF']; ?>">
          <select name="category" class="form-control input-sm">
            <option value="all">所有分类</option>
            <?php foreach ($categories as $item): ?>
              <option value="<?php echo $item['id'] ?>"<?php echo isset($_GET['category']) && $_GET['category'] == $item['id'] ? ' selected' : ''; ?>><?php echo $item['name'] ?></option>
            <?php endforeach ?>
          </select>
          <select name="status" class="form-control input-sm">
            <option value="all">所有状态</option>
            <option value="drafted"<?php echo isset($_GET['status']) && $_GET['status'] == 'drafted' ? ' selected' : ''; ?>>草稿</option>
            <option value="published"<?php echo isset($_GET['status']) && $_GET['status'] == 'published' ? ' selected' : ''; ?>>已发布</option>
            <option value="trashed"<?php echo isset($_GET['status']) && $_GET['status'] == 'trashed' ? ' selected' : ''; ?>>回收站</option>
          </select>
          <button class="btn btn-default btn-sm">筛选</button>
        </form>
        <ul class="pagination pagination-sm pull-right">
          <li><a href="#">上一页</a></li>
          <?php for ($i = $begin; $i <= $end; $i++): ?>
            <li <?php echo $i === $page ? ' class="active"' : ''; ?>><a href="?page=<?php echo $i . $search; ?>"><?php echo $i ?></a></li>
          <?php endfor ?>
          <li><a href="#">下一页</a></li>
        </ul>
      </div>
      <table class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th class="text-center" width="40"><input type="checkbox"></th>
            <th>标题</th>
            <th>作者</th>
            <th>分类</th>
            <th class="text-center">发表时间</th>
            <th class="text-center">状态</th>
            <th class="text-center" width="100">操作</th>
          </tr>
        </thead>
        <tbody>
         <?php foreach ($posts as $item): ?>
            <tr>
            <td class="text-center"><input type="checkbox"></td>
            <td><?php echo $item['title'] ?></td>
            <td><?php echo $item['user_name'] ?></td>
            <td><?php echo $item['category_name'] ?></td>
            <td class="text-center"><?php echo convert_created($item['created']) ?></td>
            <!-- 一旦输出的判断或者转换逻辑过于复杂，不建议直接写在混编位置 -->
            <td class="text-center"><?php echo convert_status($item['status']) ?></td>
            <td class="text-center">
              <a href="javascript:;" class="btn btn-default btn-xs">编辑</a>
              <a href="javascript:;" class="btn btn-danger btn-xs">删除</a>
            </td>
          </tr>
         <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php $current_page = 'posts' ?>
  <?php include 'inc/sidebar.php'; ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>NProgress.done()</script>
</body>
</html>
