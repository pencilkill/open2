<?php echo $header; ?>
<div class="contactMain">
		<div class="cb rands">
			<div class="road">
				您現在的位置：
				<a href="<?php echo HTTP_SERVER . 'index.php?route=common/home'; ?>"><span>首頁</span></a>
				<i>//</i>
				<a href="<?php echo HTTP_SERVER . 'index.php?route=doc/list'; ?>"><span>技術支援</span></a>
				<i>//</i>
				<span>Q&amp;A</span>
			</div><!--路徑-->

			<?php echo $searchform; ?>

		</div><!--路徑和搜尋-->
		<h4 class="w1000"><img src="images/support_title.jpg" alt="" /></h4>
		<div class="subContent cb">
			<div class="mb25">
				<ul class="qslist">

					<?php foreach($faqs as $f):?>

					<li><strong>【 <?php echo $f['keyword']?> 】</strong><a href="<?php echo $f['href']; ?>"><?php echo $f['title']?></a><i><?php echo $f['date_time']; ?></i></li>

					<?php endforeach; ?>

				</ul><!--Q&A列表-->
				<div class="pages">
					<?php echo $pagination; ?>
				</div>
				<!--翻頁-->

			</div>






		</div>
	</div><!--main-->
<?php echo $footer; ?>
