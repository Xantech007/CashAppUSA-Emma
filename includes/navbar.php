<div class="mainmenu-area"style="background:linear-gradient(to bottom,#d2f6f7,#f5f5f5);border-bottom:none">
		<div class="container">
			<div class="row">
				
				<div class="col-lg-12">                 
					<nav class="navbar navbar-expand-lg navbar-light">
						<?php
						$logo = "SELECT logo FROM settings";
						$logo_query = mysqli_query($con ,$logo);

						if($logo_query)
						{
							$row = mysqli_fetch_array($logo_query);
							$logo_image = $row['logo'];					
						}
						?>
						<a class="navbar-brand" href="index"style="display:flex;align-items:center;justify-content:center;flex-direction:column">
							<img src="uploads/logo/<?= $logo_image  ?>" alt="">							
						</a>
						<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main_menu" aria-controls="main_menu"
							aria-expanded="false" aria-label="Toggle navigation">
							<span class="navbar-toggler-icon"></span>
						</button>
						<div class="collapse navbar-collapse fixed-height" id="main_menu">
							<ul class="navbar-nav ml-auto">
								<li class="nav-item">
									<a class="nav-link " href="index">Home
											<div class="mr-hover-effect"></div></a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="about">About
											<div class="mr-hover-effect"></div></a>
								</li>
								
								<li class="nav-item">
									<a class="nav-link " href="faq">FAQ
											<div class="mr-hover-effect"></div></a>
								</li>
								
							
								<li class="nav-item">
									<a class="nav-link " href="contact">Contact
											<div class="mr-hover-effect"></div></a>
								</li>
							</ul>
							<?php
							if(isset($_SESSION['admin']))
							{ ?>
								<a href="admin/signin" class="base-btn2"style="background: linear-gradient(to bottom, #ff0000, #cc0000);"> Admin</a>							
						  <?php	}						
						
							else if(isset($_SESSION['auth']))
							{ ?>
								<a href="signin" class="base-btn2"style="background: linear-gradient(to bottom, #ff0000, #cc0000);"> Dashboard</a>							
						  <?php	}
						  else
						  { ?>
						  <a href="signin" class="base-btn2"style="background: linear-gradient(to bottom, #ff0000, #cc0000);"> Login</a>
						 <?php }
							?>
							
							
						
						</div>
					</nav>
				</div>
			</div>
		</div>
	</div>
	<!--Main-Menu Area Start-->
	<div style="margin-top:60px;position:fixed;top:0px;width:100%;z-index:500">
	</div>
