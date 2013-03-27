<?php echo $header; ?>


<div class="contactMain">
		<div class="cb rands">
			<div class="road">
				您現在的位置：
				<a href="index.php?route=common/home"><span>首頁</span></a>
				<i>//</i>
				<a href="index.php?route=news/newses"><span>最新消息</span></a>
				<i>//</i>
				<span><?php echo $title; ?></span>
			</div><!--路徑-->

			<?php echo $searchform; ?>

		</div><!--路徑和搜尋-->
		<h4 class="w1000"><img src="images/news_title.jpg" alt="" /></h4>
		<div class="subContent cb">
			<div class="mb25">
				<h4 class="newsTitle"><?php echo $title; ?></h4>
				<p class="newsDate"><?php echo $addtime; ?></p>
				<div class="newsContent">

					<?php echo $description; ?>
				</div>
				<div class="pages">

				<?php if(isset($last_href)):?>
					<a href="<?php echo $last_href; ?>" class="prev"></a>
				<?php endif; ?>

				<?php if(isset($next_href)):?>
					<a href="<?php echo $next_href; ?>" class="next"></a>
				<?php endif; ?>

				</div>
				<!--翻頁-->

			</div>






		</div>
	</div><!--main-->

<?php echo $footer; ?>