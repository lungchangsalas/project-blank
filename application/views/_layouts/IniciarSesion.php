<!DOCTYPE html>
<html lang="es">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="author" content="<?php echo COMPANY_NAME; ?>">
    <title><?php echo COMPANY_NAME; ?></title>
    <base href="<?php echo $base_url; ?>" />
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/js/datatables.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/responsive.bootstrap.css">

    <script src="/assets/js/jquery.js"></script>
    <script  src="/assets/js/popper.min.js"></script>
    <script src="/assets/plotly/dist/plotly.min.js"></script>
    <script src="/assets/js/bootstrap.min.js"></script>
    <script type="text/javascript" charset="utf8" src="/assets/js/datatables.js"></script>
    <script type="text/javascript" charset="utf8" src="/assets/js/dataTables.responsive.js"></script>
    <!-- custom -->
    <link rel="stylesheet" href="/assets/css/login.css">
  </head>
  <body>
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <?php $this->load->view($inner_view); ?>
            </div>
        </div>
    </div>
  </body>
</html>