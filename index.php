<?php
	ob_start();
	session_start();
	include('functions.php');
	
	$page_request = @$_REQUEST['page'];
	$task_request = @$_REQUEST['task'];
	if (isset($page_request))
	{
		$current_page = $page_request;
	}
	else
	{
		$current_page = "home";
	}
	
	switch($current_page)
	{
		case "home":
			$page_title = "Home";
			
			$home_active = ' class="active_link"';
			$employers_active = '';
			$workedhours_active = '';
			$payments_active = '';
		break;
		case "employers":
			$page_title = "Employers";
			
			$home_active = '';
			$employers_active = ' class="active_link"';
			$workedhours_active = '';
			$payments_active = '';
		break;
		case "workedhours":
			$page_title = "Worked hours";
			
			$home_active = '';
			$employers_active = '';
			$workedhours_active = ' class="active_link"';
			$payments_active = '';
		break;
		case "payments":
			$page_title = "Payments";
			
			$home_active = '';
			$employers_active = '';
			$workedhours_active = '';
			$payments_active = ' class="active_link"';
		break;		
		default:
			$page_title = "Home";
			
			$home_active = ' class="active_link"';
			$employers_active = '';
			$workedhours_active = '';
			$payments_active = '';
	}
	
	?><!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $page_title; ?> | Work Management System</title>
<link href="css/styles.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/util.js"></script>
<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
<script type="text/javascript" src="js/jquery.min.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
</head>

<body>

<div class="container">
  <header>
    <div class="header_text">
        <h1><a href="#">Work Management System</a></h1>
        <!--<h3><?php echo $page_title; ?></h3>-->
    </div>
  <div class="sidebar1"> 
    <nav>
      <ul>
        <li><a href="?page=home"<?php echo $home_active; ?>>Home</a></li>
        <li><a href="?page=employers"<?php echo $employers_active; ?>>Employers</a>
            <ul>
            	<li><a href="?page=employers&task=view">View</a></li>
                <li><a href="?page=employers&task=add">Add</a></li>
            </ul>
        </li>
        <li><a href="?page=workedhours"<?php echo $workedhours_active; ?>>Worked Hours</a>
            <ul>
            	<li><a href="?page=workedhours&task=view&clearFilter=1">View</a></li>
                <li><a href="?page=workedhours&task=add">Add</a></li>
            </ul>
        </li>
        <li><a href="?page=payments"<?php echo $payments_active; ?>>Payments</a>
            <ul>
            	<li><a href="?page=payments&task=view">View</a></li>
                <li><a href="?page=payments&task=add">Add</a></li>
            </ul>
        </li>
      </ul>     
    </nav>
  </div> <!-- end .sidebar1 -->   
  </header>
  
  
  <article class="content">
  <?php
  	$task = $current_page . "|" . $task_request;
	switch($task)
	{
		case "employers|":
			$employers = new Employers();
			$employers->index();
		break;
		case "employers|add":
			$employers = new Employers();
			$employers->add();
		break;
		case "employers|edit":
			$employers = new Employers();
			$employers->edit(@$_REQUEST['id']);
		break;
		case "employers|delete":
			$employers = new Employers();
			$employers->delete(@$_REQUEST['id']);
		break;
		case "employers|view":
			$employers = new Employers();
			$employers->view();
		break;	
		case "workedhours|":
			$workedHours = new WorkedHours();
			$workedHours->index();
		break;
		case "workedhours|add":
			$workedHours = new WorkedHours();
			$workedHours->add();
		break;
		case "workedhours|edit":
			$workedHours = new WorkedHours();
			$workedHours->edit(@$_REQUEST['id']);
		break;
		case "workedhours|delete":
			$workedHours = new WorkedHours();
			$workedHours->delete(@$_REQUEST['id']);
		break;
		case "workedhours|view":
			$workedHours = new WorkedHours();
			$workedHours->view();
		break;	
		case "payments|":
			$payments = new Payments();
			$payments->index();
		break;
		case "payments|add":
			$payments = new Payments();
			$payments->add();
		break;
		case "payments|edit":
			$payments = new Payments();
			$payments->edit(@$_REQUEST['id']);
		break;	
		case "payments|delete":
			$payments = new Payments();
			$payments->delete(@$_REQUEST['id']);
		break;
		case "payments|view":
			$payments = new Payments();
			$payments->view();
		break;		
		case "home|":	
		default:
			echo "<h1>Pick a task</h1>";
			$employers = new Employers();
			$employers->index();
			
			$workedHours = new WorkedHours();
			$workedHours->index();
			
			$payments = new Payments();
			$payments->index();
	}
  ?>
    </article>
  <footer>
    <p>Work Management System - Copyright © 2013. All rights reserved. </p>
  </footer>
  <!-- end .container --></div>
</body>
</html><?php ob_end_flush(); ?>