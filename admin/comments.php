<?php 

require_once '../functions.php';

bx_get_current_user();

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Comments &laquo; Admin</title>
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
        <h1>所有评论</h1>
      </div>
      <!-- 有错误信息时展示 -->
      <!-- <div class="alert alert-danger">
        <strong>错误！</strong>发生XXX错误
      </div> -->
      <div class="page-action">
        <!-- show when multiple checked -->
        <div class="btn-batch" style="display: none">
          <button class="btn btn-info btn-sm">批量批准</button>
          <button class="btn btn-warning btn-sm">批量拒绝</button>
          <button class="btn btn-danger btn-sm">批量删除</button>
        </div>
        <ul class="pagination pagination-sm pull-right"></ul>
      </div>
      <table class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th class="text-center" width="40"><input type="checkbox"></th>
            <th>作者</th>
            <th>评论</th>
            <th>评论在</th>
            <th>提交于</th>
            <th>状态</th>
            <th class="text-center" width="150">操作</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <?php $current_page = 'comments' ?>
  <?php include 'inc/sidebar.php'; ?>

  <script id="comments_tmpl" type="text/x-jsrender">
    {{for comments}}
    {{!-- <tr><td>{{:#index}}</td><td>{{:content}}</td></tr> --}}
    <tr{{if status == 'held'}} class = "warning" {{else status == 'rejected'}} class = "danger" {{/if}} data-id="{{:id}}">
      <td class="text-center"><input type="checkbox"></td>
      <td>{{:author}}</td>
      <td>{{:content}}</td>
      <td>《{{:post_title}}》</td>
      <td>{{:created}}</td>
      <td>{{:status}}</td>
      <td class="text-center">
        {{if status == 'held'}}
        <a href="post-add.html" class="btn btn-success btn-xs">批准</a>
        <a href="post-add.html" class="btn btn-warning btn-xs">驳回</a>
        {{/if}}
        <a href="javascript:;" class="btn btn-danger btn-xs btn-delete">删除</a>
      </td>
    </tr>
    {{/for}}
  </script>
  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script src="/static/assets/vendors/jsrender/jsrender.js"></script>
  <script src="/static/assets/vendors/twbs-pagination/jquery.twbsPagination.js"></script>
  <script>
    // nprogress
    // 进度条
    $(document)
     .ajaxStart(function () {
       NProgress.start()
     })
     .ajaxStop(function () {
       NProgress.done()
     })

    // 发送AJAX 请求获取列表所需数据
    // $.getJSON('/admin/api/comments.php', {page: 2}, function (res) {
    //   var html = $('#comments_tmpl').render({comments : res})
    //   $('tbody').html(html)

    //   // 请求得到响应过后自动执行
    //   // 将数据渲染到页面上
    //   // 准备一个给摸板使用的数据
    //   // var data = {};
    //   // data.comments =res;
    //   // var html = $('#comments_tmpl').render(data)
    //   // console.log(html);
    // })

    var currentPage = 1

    function loadPageData (page) {
      $.getJSON('/admin/api/comments.php', {page: page}, function (res) {
        if (page > res.total_pages) {
          loadPageData(res.total_pages)
          return
        }
        $('.pagination').twbsPagination('destroy')
        $('.pagination').twbsPagination({
          startPage: page,
          totalPages: res.total_pages,
          visiablePages: 5,
          initiateStartPageClick: false,
          onPageClick: function (e, page) {
            // 第一次初始化，就会触发一次
            loadPageData(page)
          }
        })

        var html = $('#comments_tmpl').render({comments: res.comments})
        $('tbody').fadeOut(function () {
          $(this).html(html).fadeIn()
        })
        currentPage = page
      })
    }
    loadPageData(currentPage)

    // 删除功能
    $('tbody').on('click', '.btn-delete', function () {
      var $tr = $(this).parent().parent()
      var id = $tr.data('id')
      $.get('/admin/api/comment-delete.php', {id: id}, function (res) {
        if (!res) return
        loadPageData(currentPage)
      })
    })
  </script>
  <script>NProgress.done()</script>
</body>
</html>
