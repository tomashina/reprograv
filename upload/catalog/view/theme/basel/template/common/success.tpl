<?php echo $header; ?>
<div class="container">
  <ul class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
    <?php } ?>
  </ul>
  <div class="row"><?php echo $column_left; ?>
    <?php if ($column_left && $column_right) { ?>
    <?php $class = 'col-sm-6'; ?>
    <?php } elseif ($column_left || $column_right) { ?>
    <?php $class = 'col-md-9 col-sm-8'; ?>
    <?php } else { ?>
    <?php $class = 'col-sm-12'; ?>
    <?php } ?>
    <div id="content" class="<?php echo $class; ?>"><?php echo $content_top; ?>
      <h1 id="page-title"><?php echo $heading_title; ?></h1>
      <div class="success-page-card">
        <span class="success-page-icon" aria-hidden="true"><i class="fa fa-check"></i></span>
        <div class="success-page-copy"><?php echo $text_message; ?></div>
        <?php if (!empty($scan)) { ?>
        <div class="success-page-scan"><img src="<?php echo $scan; ?>" alt="" /></div>
        <?php } ?>
        <?php if (!empty($continue) && !empty($button_continue)) { ?>
        <div class="success-page-actions">
          <a href="<?php echo $continue; ?>" class="btn btn-primary"><?php echo $button_continue; ?> <i class="fa fa-angle-right" aria-hidden="true"></i></a>
        </div>
        <?php } ?>
      </div>
      <?php echo $content_bottom; ?></div>
    <?php echo $column_right; ?></div>
</div>
<?php echo $footer; ?>
