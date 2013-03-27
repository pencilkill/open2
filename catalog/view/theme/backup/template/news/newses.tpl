<?php echo $header; ?>

<div class="contactMain">
		<div class="cb rands">
			<div class="road">
				您現在的位置：
				<a href="index.php?route=common/home"><span>首頁</span></a>
				<i>//</i>
				<span>最新消息</span>
			</div><!--路徑-->

			<?php echo $searchform; ?>

		</div><!--路徑和搜尋-->
		<h4 class="w1000"><img src="images/news_title.jpg" alt="" /></h4>
		<div class="subContent cb">
			<div class="mb25">
				<ul class="newslist">

					<?php foreach($newses as $n):?>

					<li><a href="<?php echo $n['href']; ?>"><?php echo $n['title']; ?></a><i><?php echo $n['date_time']; ?></i></li>

					<?php endforeach;?>

				</ul><!--Q&A列表-->
				<div class="pages">
					<?php echo $pagination; ?>
				</div>
				<!--翻頁-->

			</div>

		</div>
	</div><!--main-->
<?php echo $footer; ?>