<?php

	if (isset($_GET['news'])) {
		include_once "core/db_connect.php";
		include_once "include/auth.php";
		
		$result_user_is_admin = mysql_query("SELECT is_admin FROM users WHERE email = '".$_COOKIE["user"]."'") or die(mysql_error());
		while ($user_is_admin = mysql_fetch_assoc($result_user_is_admin)) {
			$is_admin = $user_is_admin['is_admin'];
		}
		
		$month_name = array( 1 => 'января', 2 => 'февраля', 3 => 'марта', 
		4 => 'апреля', 5 => 'мая', 6 => 'июня', 
		7 => 'июля', 8 => 'августа', 9 => 'сентября', 
		10 => 'октября', 11 => 'ноября', 12 => 'декабря' 
				   );
	
		$curdate = date("Y-m-d");
						
		$result_news = mysql_query("SELECT * FROM news WHERE id = ".$_GET['news']) or die(mysql_error());
		
		if (isset($_POST['addCommentNewsId']) && strlen($_POST['addCommentNewsId']) != 0 && $_POST['addCommentNewsId'] != 0) {
			$input_text = strip_tags($_POST['addComment']);
			$input_text = htmlspecialchars($input_text);
			$input_text = mysql_escape_string($input_text);
			mysql_query("INSERT INTO news_comments SET news = ".$_POST['addCommentNewsId'].", user = (SELECT id FROM users WHERE email = '".$_COOKIE["user"]."'), comment = '".$input_text."'") or die(mysql_error());
		}
		
		if (isset($_GET['del_comment']) && strlen($_GET['del_comment']) != 0 && $_GET['del_comment'] != 0) {
			mysql_query("UPDATE news_comments SET is_del = 1 WHERE id = ". $_GET['del_comment']) or die(mysql_error());
			header("Location: news.php?news=".$_GET['news']."#comments");
		}
	

?>


<!DOCTYPE html>
<html lang="ru">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Система управления СНТ</title>
		<script src="http://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
		<link rel="stylesheet" href="css/font-awesome.min.css">
		<link rel="stylesheet" href="css/sweetalert.css">
		<script src="js/sweetalert.min.js"></script>
		<link rel="stylesheet" href="css/my.css">
		<style>
			#header {
				background: url(img/header.jpg);
				min-height: 280px;
				background-size: cover;
				background-repeat: no-repeat;
			}
			.news_date {
				color: #777;
			}
			img {
				width: 100%;
			}
			.comment {
				padding: 10px;
				border-radius: 5px;
				margin-bottom: 10px;
			}
			.comment-header {
				border-radius: 5px;
			}
			
			.comment-header table tr td {
				padding: 5px;
			}
			.comment-body {
				padding-top: 10px;
			}
			.del_user{
				color:Crimson;
			}
			.del_user:hover{
				color:red;
			}
			.bg-header-success {
				background:#a1f180;
			}
		</style>
		
	</head>
	<body>
		
		<?php include_once "include/head.php"; ?>
		
		
		
		<div class="jumbotron" id="header">
		  <div class="container" >
			
			
		  </div>
		</div>
		
		<div class="container">
			
			<div class="row">
				<div class="col-md-12">
				  
				  <?php
					while ($news = mysql_fetch_assoc($result_news)) {
						$newsID = $news['id'];
						$newsDiscussed = $news['discussed'];
						$time = strtotime($news['date_crate']);
						$month = $month_name[ date( 'n',$time ) ]; 
						$day   = date( 'j',$time ); 
						$year  = date( 'Y',$time ); 
						
						$news_date = "[$day $month $year]";
						
						$text = $news['text'];

						
											
						echo '<h3>'.$news['header'].'</h3>';
						echo '<span class="news_date">'.$news_date.'</span>';
						echo '<p>'. $text .'</p>';
						
					}
				  ?>
				</div>
			</div>
			<hr>
			<?php
			if ($is_auth == 1 && $newsDiscussed == 1) {
			?>
			<div class="row" id="comments">
				<div class="col-md-12">
					<h2>Обсуждение</h2>
					<div class="panel panel-default">
						<div class="panel-heading spoiler-trigger" data-toggle="collapse" style="padding: 0; border: none; background: none;">
							<button type="button" class="btn btn-default spoiler-trigger" data-toggle="collapse" style="width: 100%; box-shadow: none; border: none; border-radius: 0;">
								Добавить комментарий <i class="fa fa-chevron-down" aria-hidden="true"></i>
							</button>
						</div>
						<div class="panel-collapse collapse out">
							<div class="panel-body">
								<form method="POST">
									<input name="addCommentNewsId" type="hidden" value="<?php echo $newsID; ?>">
									<div class="form-group">
										<label for="addComment">Текст комментария</label>
										<textarea name="addComment" class="form-control" rows="3" id="addComment"></textarea>
									</div>
									<button type="submit" class="btn btn-default">Сохранить</button>
								</form>
							</div>
						</div>
					</div>
					<p></p>
					<?php
					$result_comments = mysql_query("SELECT nc.id, nc.comment, nc.datetime, u.name, u.email FROM news_comments nc, users u WHERE nc.user = u.id AND nc.news = $newsID AND nc.is_del = 0 ORDER BY datetime") or die(mysql_error());
					
					while ($comments = mysql_fetch_assoc($result_comments)) {
							if ($comments['email'] == $_COOKIE["user"]) {
								$bgComment = 'bg-success';
								$headerComment = 'bg-header-success';
							}
							else {
								$bgComment = 'bg-info';
								$headerComment = 'bg-primary';
							}
							echo '<div class="col-md-10 '.$bgComment.' comment">';
								echo '<div class="'.$headerComment.' comment-header">';
									echo '<table style="width: 100%;">';
										echo '<tr>';
											echo '<td><b>'.$comments['name'].'</b>  ['.date( 'd.m.Y G:i',strtotime($comments['datetime'])).']</td>';
											echo '<td style="text-align: right;">';
											if ($is_admin == 1) {
												echo '<a class="del_user" href="#comments" onclick="ConfirmDelComment(\''.$comments['id'].'\',\''.$_GET['news'].'\')"><i class="fa fa-trash" aria-hidden="true" title="Удалить комментарий"></i></a>';
											}
											echo '</td>';
										echo '</tr>';
									echo '</table>';
								echo '</div>';
								echo '<div class="comment-body">';
									echo '<p>'.$comments['comment'].'</p>';
								echo '</div>';
							echo '</div>';
					}					
					?>
					<script>
					function ConfirmDelComment(comment_id,news_id) {
						swal({
							title: 'Удалить комментарий?',
							text: '',
							type: 'warning',
							showCancelButton: true,
							confirmButtonColor: '#dd6b55',
							cancelButtonColor: '#999',
							confirmButtonText: 'Да, удалить',
							cancelButtonText: 'Отмена',
							closeOnConfirm: false
						}, function() {
							swal(
							  'Выполнено!',
							  'Комментарий удален.',
							  'success'
							);
							document.location.href = "news.php?news="+news_id+"&del_comment="+comment_id+"#comments";
						})
					}
					</script>
				</div>
			</div>
				
			<?php
			}			
			?>
		  
		  
		</div>
		<?php include_once "include/footer.php"; ?>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/script.js"></script>
	</body>
</html>









<?php	
	}
?>
	
	
	
	