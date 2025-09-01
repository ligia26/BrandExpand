<div class="sidebar-wrapper" data-simplebar="true" class="color-sidebar sidebarcolor2">
    <div class="sidebar-header">
        <div class="logo-text">
            <img src="assets/images/logo-full.png" class="logo-icon" alt="logo icon">
        </div>
        <div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i>
        </div>
    </div>
    <ul class="metismenu" id="menu">
        <li>
            <a href="index.php">
                <div class="parent-icon"><i class='bx bx-home-alt'></i></div>
                <div class="menu-title">Dashboard</div>
            </a>

           
        </li>

        <?php // if ($_SESSION['user_role'] === 1000): ?>
            <li>
                <a class="has-arrow" href="javascript:;">
                    <div class="parent-icon"><i class="bx bx-grid-alt"></i></div>
                    <div class="menu-title">Clients Setup</div>
                </a>
                <ul>
                    <li><a href="users.php"><i class='bx bx-radio-circle'></i> Users</a></li>
               <!--     <li><a href="companies.php"><i class='bx bx-radio-circle'></i>Companies</a></li>
                    <li><a href="domains.php"><i class='bx bx-radio-circle'></i>Domains</a></li>
                    <li><a href="countries.php"><i class='bx bx-radio-circle'></i>Countries</a></li>
                    <li><a href="users_domain.php"><i class='bx bx-radio-circle'></i>Users Domain</a></li>

        -->
                </ul>
            </li>

           
        <?php // endif; ?>
        <li class="menu-label">Content Setup</li>
        <li>
            <a class="has-arrow" href="javascript:;">
                <div class="parent-icon"><i class="bx bx-network-chart"></i></div>
                <div class="menu-title">Content Setup</div>
            </a>
            <ul>
              <!--  <li><a href="setup.php"><i class='bx bx-radio-circle'></i>Current Setup</a></li> -->
                <li><a href="setup.php"><i class='bx bx-radio-circle'></i>Add Setup</a></li>
                <li><a href="connect_platforms.php"><i class='bx bx-radio-circle'></i>Connect Platforms</a></li>

            </ul>
        </li>



        <li class="menu-label">Content Data</li>
        <li>
            <a class="has-arrow" href="javascript:;">
                <div class="parent-icon"><i class="bx bxs-paper-plane"></i></div>
                <div class="menu-title">Content Data</div>
            </a>
            <ul>
              <!--  <li><a href="setup.php"><i class='bx bx-radio-circle'></i>Current Setup</a></li> -->
                <li><a href="posts.php"><i class='bx bx-radio-circle'></i>Articles</a></li>
                <li><a href="news_letter.php"><i class='bx bx-radio-circle'></i>News Letter</a></li>


            </ul>
        </li>


        <li class="menu-label">System Setup</li>
        <li>
            <a class="has-arrow" href="javascript:;">
                <div class="parent-icon"><i class="bx bx-cog"></i></div>
                <div class="menu-title">System Setup</div>
            </a>
            <ul>
              <!--  <li><a href="setup.php"><i class='bx bx-radio-circle'></i>Current Setup</a></li> -->
                <li><a href="prompts.php"><i class='bx bx-radio-circle'></i>Prompts</a></li>
                <li><a href="personalities.php"><i class='bx bx-radio-circle'></i>Personalities</a></li>
                <li><a href="main_setup.php"><i class='bx bx-radio-circle'></i>APIS</a></li>


            </ul>
        </li>



 

<!--
        <li>
            <a class="has-arrow" href="javascript:;">
                <div class="parent-icon"><i class="bx bx-edit"></i></div>
                <div class="menu-title">Generate Content</div>
            </a>
            <ul>
                <li><a href="rules_list.php"><i class='bx bx-radio-circle'></i>Generate and Sent Content</a></li>
            </ul>
        </li>

            <li>
                <a href="access_log.php">
                    <div class="parent-icon"><i class="bx bx-help-circle"></i></div>
                    <div class="menu-title">Master Configurations</div>
                </a>
            </li>
-->
           
    </ul>
    <!--end navigation-->
</div>
