<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php include('../../template.php'); ?>
<html>
    <head>
        <meta name="generator" content="HTML Tidy, see www.w3.org">
        <meta http-equiv="Content-Type" content=
        "text/html; charset=iso-8859-1">

        <title><?php echo PRODUCT_NAME; ?> Documentation</title>
        <link rel="stylesheet" type="text/css" href="../../css/doc.css">
        <meta http-equiv="Content-Type" content="text/html">
		<meta name="keywords" content="digital signage, signage, narrow-casting, <?php echo PRODUCT_NAME; ?>, open source, agpl" />
		<meta name="description" content="<?php echo PRODUCT_NAME; ?> is an open source digital signage solution. It supports all main media types and can be interfaced to other sources of data using CSV, Databases or RSS." />
        <link href="img/favicon.ico" rel="shortcut icon">
        <!-- Javascript Libraries -->
		<script type="text/javascript" src="lib/jquery.pack.js"></script>
		<script type="text/javascript" src="lib/jquery.dimensions.pack.js"></script>
		<script type="text/javascript" src="lib/jquery.ifixpng.js"></script>
    </head>

    <body>
        <h1>Users</h1>
        
        <p>All actions in <?php echo PRODUCT_NAME; ?> - content creation, schedules etc - are attributed to a user. This information, along with a 
        	permissions system, allows users of the system to share things they have created in the system with each other and 
        	also allows "admins" of the system to oversee what is being shown on displays. Users also have a "Home Page". 
        	This will become their "Dashboard" page. Using the home page users can be directed to a simple page allowing very 
        	restricted access to <?php echo PRODUCT_NAME; ?> - or a complex page showing all components available.</p> 
        
		
		<p>During initial account set up, a admin user was created for you. This account can be used to create additional users 
			and alter <?php echo PRODUCT_NAME; ?>'s settings.</p>
		
		<p>The built in user model has the following components:</p>
			<ul>
				<li>User Administration - define name, password, email, homepage, etc</li>
				<li>User Types - advanced permissions</li>
				<li>User Groups and Group/Component Permissions</li>
			</ul>
		<p>Each of these topics is discussed in detail in this section of the manual</p>

		<?php include('../../template/footer.php'); ?>
    </body>
</html>
