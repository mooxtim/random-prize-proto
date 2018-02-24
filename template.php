<?php

class template
{
	public function top()
	{
	?>
<!DOCTYPE html>
<html>
	<head>
	<title>Get Prize, prototype</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col-sm-12">

	<?php
	}

	public function bottom()
	{
		?>
				</div>
			</div>
		</div>
	</body>
</html>
		<?php
	}
	public function result(array $prize) 
	{
		extract($prize);
		?>
		<div>
			<h1>You are Win:</h1>
		<?php if ($type == 'points') { ?>
			<h3><?= $count ?> points!</h3>

			<p><a href="?r=refuse&amp;id=<?= $prize_id ?>"><button class="btn btn-danger">Refuse</button></a></p>
			<p><a href="?r=get&amp;id=<?= $prize_id ?>"><button class="btn btn-primary">Get My Points!</button></a></p>
		<?php } else if ($type == 'money') { ?>
			<h3>€<?= $count ?>!</h3>
			<p><a href="?r=refuse&amp;id=<?= $prize_id ?>"><button class="btn btn-danger">Refuse</button></a></p>
			<p><a href="?r=get&amp;id=<?= $prize_id ?>"><button class="btn btn-primary">Get My Money!</button></a></p>
			<p><a href="?r=convert&amp;id=<?= $prize_id ?>"><button class="btn btn-success">Convert to Points!</button></a></p>
		<?php } else { ?>
			<h3><?= $name ?>!</h3>
			<a href="?r=refuse&amp;id=<?= $prize_id ?>"><button class="btn btn-danger">Refuse</button></a>
			<a href="?r=get&amp;id=<?= $prize_id ?>"><button class="btn btn-success">Get My <?= $name ?>!</button></a>
		<?php } ?>
		</div>
		<?php
		
	}
	
	public function complete($prize)
	{
		extract($prize);
		?>
		<div>
		<?php if ($type == 'money') { ?>
			<h3>The money has been sent to your bankcard</h3>
		<?php } else if ($type == 'points') { ?>
			<h3>Points credited to your account!</h3>
		<?php } else { ?>
			<h3>Your prize will be send</h3>
		<?php } ?>
		<p><a href="?"><button class="btn btn-primary">Back</button></a></p>
		</div>
		<?php
	}
	
	public function complete_convert($points)
	{
		?>
		<h3>You have received <?= $points ?> points</h3>
		<p><a href="?"><button class="btn btn-primary">Back</button></a></p>
		<?php
	}
	
	public function complete_refuse()
	{
		?>
		<h3>You refused the prize</h3>
		<p><a href="?"><button class="btn btn-primary">Back</button></a></p>
		<?php
	}

	public function index() 
	{
	?>	
		<div>
			<h3>Prize</h3>
			<p id="get-button-area"><a href="?r=getRandom"><button class="btn btn-primary">Get Random!</button></a></p>
			<p id="result-area"></p>
		</div>
	<?php
	}

}