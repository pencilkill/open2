<?php echo $header; ?>
<div class="contactMain">
		<div class="cb rands">
			<div class="road">
				您現在的位置：
				<a href="<?php echo HTTP_SERVER . 'index.php?route=common/home'; ?>"><span>首頁</span></a>
				<i>//</i>
				<a href="<?php echo HTTP_SERVER . 'index.php?route=doc/list'; ?>"><span>技術支援</span></a>
				<i>//</i>
				<a href="<?php echo HTTP_SERVER . 'index.php?route=faq/list'; ?>"><span>Q&amp;A</span></a>
				<i>//</i>
				<span><?php echo $title; ?></span>
			</div><!--路徑-->

			<?php echo $searchform; ?>

		</div><!--路徑和搜尋-->
		<h4 class="w1000"><img src="images/support_title.jpg" alt="" /></h4>
		<div class="subContent cb">
			<div class="mb25">
				<h4 class="qaTitle">【 <?php echo $keyword; ?> 】<?php echo $title; ?></h4>
				<p class="qaContent">
					<?php echo $description; ?>
				</p>
				<div class="pages">

					<?php if(isset($last_href)):?>
					<a href="<?php echo $last_href; ?>" class="prev"></a>
					<?php endif;?>

					<?php if(isset($next_href)):?>
					<a href="<?php echo $next_href; ?>" class="next"></a>
					<?php endif;?>
				</div>
				<!--翻頁-->

			</div>






		</div>
	</div><!--main-->
<?php echo $footer; ?>
