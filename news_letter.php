<!doctype html>
<html lang="en" class="color-sidebar sidebarcolor2">
<?php
session_start(); // Start the session
include "includes/head.php"; 
include 'includes/db.php'; // Include your database connection file

// Initialize variables
$subject = '';
$message = '';

// Fetch articles from the database
$articles = [];
$result = mysqli_query($connection, "SELECT id, title FROM posts");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $articles[] = $row;
    }
}

// Fetch segments from Mautic
$mautic_url = "https://m.datainnovation.io"; // Replace with your Mautic URL
$username = "admin";     // Replace with your Mautic username
$password = "mW4{oF0~DIrE0.hY9}";     // Replace with your Mautic password

// Initialize cURL
$ch = curl_init();

// Set the URL for fetching segments
curl_setopt($ch, CURLOPT_URL, $mautic_url . "/api/segments");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Basic " . base64_encode("$username:$password")
]);

// Execute cURL request
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Close cURL
curl_close($ch);

// Handle response
if ($http_code === 200) {
    $segmentsData = json_decode($response, true);
    $segments = $segmentsData['lists'];
} else {
    $_SESSION['error_message'] = "Failed to fetch segments. Response: " . $response;
    $segments = [];
}

// Handle Stage One: Generate Newsletter
if (isset($_POST['generate_newsletter'])) {
    $article_id = $_POST['article_id'];

    if (!empty($article_id)) {
        // Fetch the article from the database
        $stmt = $connection->prepare("SELECT title, body FROM posts WHERE id = ?");
        $stmt->bind_param("i", $article_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $article = $result->fetch_assoc();

        if ($article) {
            // Send the article to ChatGPT
            $prompt = "Please create a fantastic newsletter HTML version  of the following article :\n\nTitle: " . $article['title'] . "\n\nContent:\n" . $article['body'];

            // Call ChatGPT API

            $apiKey = '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'; // Replace with your OpenAI API key
            $openaiUrl = 'https://api.openai.com/v1/chat/completions';

            $data = array(
                'model' => 'gpt-4',
                'messages' => array(
                    array('role' => 'user', 'content' => $prompt)
                ),
                'max_tokens' => 3000,
                'temperature' => 0.7,
            );

            $headers = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $openaiUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            curl_close($ch);

            $responseData = json_decode($response, true);

            if (isset($responseData['choices'][0]['message']['content'])) {
                $generatedNewsletter = $responseData['choices'][0]['message']['content'];
                // Store the generated content in a session variable
                $_SESSION['generated_newsletter'] = $generatedNewsletter;
                $_SESSION['success_message'] = "Newsletter generated successfully.";
                // Redirect to the send email page
                header("Location: news_letter.php");
                exit();
            } else {
                // Handle API error
                $_SESSION['error_message'] = "Error generating newsletter.";
            }
        } else {
            $_SESSION['error_message'] = "Article not found.";
        }
    } else {
        $_SESSION['error_message'] = "Please select an article.";
    }
}

// Handle Stage Two: Send Email
if (isset($_POST['send_email'])) {
    // Get form data
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $segment_id = $_POST['segment_id'];

    // Mautic Basic Auth credentials
    $mautic_url = "https://m.datainnovation.io"; // Replace with your Mautic URL
    $username = "admin";     // Replace with your Mautic username
    $password = "mW4{oF0~DIrE0.hY9}";     // Replace with your Mautic password
    

    $url = $mautic_url . "/api/emails/new";

    // Email payload
    $data = [
        'name' => 'Newsletter - ' . date('Y-m-d H:i:s'),
        'subject' => $subject,
        'customHtml' => $message,
        'emailType' => 'list', // 'list' for segment email
        'lists' => [$segment_id], // Assign segment
        'isPublished' => true,
        'publishUp' => date('Y-m-d H:i:s'), // Now in 'Y-m-d H:i:s' format
    ];

    // Initialize cURL
    $ch = curl_init();

    // cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Basic " . base64_encode("$username:$password")
    ]);

    // Execute cURL request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Close cURL
    curl_close($ch);

    // Handle response
    if ($http_code === 200 || $http_code === 201) {
        $_SESSION['success_message'] = "Email sent successfully to the selected segment.";
        // Clear form data and session variable after successful send
        $subject = '';
        $message = '';
        unset($_SESSION['generated_newsletter']);
    } else {
        $errorResponse = json_decode($response, true);
        $errorMessage = isset($errorResponse['errors'][0]['message']) ? $errorResponse['errors'][0]['message'] : 'An unknown error occurred.';
        $_SESSION['error_message'] = "Failed to send email. Error: " . $errorMessage;
    }
}

?>
<body>
    <!--wrapper-->
    <div class="wrapper">
        <!--sidebar wrapper -->
        <?php include "includes/side_menu.php"; ?>
        <!--end sidebar wrapper -->
        <!--start header -->
        <?php include "includes/header.php"; ?>
        <!--end header -->
        <!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">
                <!--breadcrumb-->
                <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                    <div class="breadcrumb-title pe-3">Clients Dashboard</div>
                    <div class="ps-3">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 p-0">
                                <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Send Emails</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <!--end breadcrumb-->

                <!-- Alerts -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <!-- Stage One: Generate Newsletter -->
                        <?php if (!isset($_SESSION['generated_newsletter'])): ?>
                            <div class="card">
                                <div class="card-body p-4">
                                    <h5 class="mb-4">Generate Newsletter</h5>
                                    <form action="" method="post">
                                        <input type="hidden" name="generate_newsletter" value="1">
                                        <div class="row mb-3">
                                            <label for="articleSelect" class="col-sm-3 col-form-label">Select Article</label>
                                            <div class="col-sm-9">
                                                <select class="form-control" name="article_id" id="articleSelect" required>
                                                    <option value="">-- Select an Article --</option>
                                                    <?php foreach ($articles as $article): ?>
                                                        <option value="<?php echo $article['id']; ?>"><?php echo htmlspecialchars($article['title']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label"></label>
                                            <div class="col-sm-9">
                                                <button type="submit" class="btn btn-primary px-4">Generate Newsletter</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Stage Two: Send Email -->
                            <div class="card">
                                <div class="card-body p-4">
                                    <h5 class="mb-4">Send Email</h5>
                                    <form action="" method="post">
                                        <input type="hidden" name="send_email" value="1">
                                        <!-- Segment Selection -->
                                        <div class="row mb-3">
                                            <label for="segmentSelect" class="col-sm-3 col-form-label">Select Segment</label>
                                            <div class="col-sm-9">
                                                <select class="form-control" name="segment_id" id="segmentSelect" required>
                                                    <option value="">-- Select a Segment --</option>
                                                    <?php foreach ($segments as $segment): ?>
                                                        <option value="<?php echo $segment['id']; ?>"><?php echo htmlspecialchars($segment['name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <!-- Subject -->
                                        <div class="row mb-3">
                                            <label for="emailSubject" class="col-sm-3 col-form-label">Subject</label>
                                            <div class="col-sm-9">
                                                <div class="position-relative input-icon">
                                                    <input type="text" class="form-control" name="subject" id="emailSubject" placeholder="Email Subject" value="<?php echo htmlspecialchars($subject); ?>" required>
                                                    <span class="position-absolute top-50 translate-middle-y"><i class='bx bx-edit'></i></span>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Message -->
                                        <div class="row mb-3">
                                            <label for="emailBody" class="col-sm-3 col-form-label">Message</label>
                                            <div class="col-sm-9">
                                                <textarea class="form-control" name="message" id="emailBody" rows="10"><?php echo isset($_SESSION['generated_newsletter']) ? htmlspecialchars($_SESSION['generated_newsletter']) : ''; ?></textarea>
                                                <small class="text-muted">You can modify the generated newsletter before sending.</small>
                                            </div>
                                        </div>
                                        <!-- Buttons -->
                                        <div class="row">
                                            <label class="col-sm-3 col-form-label"></label>
                                            <div class="col-sm-9">
                                                <div class="d-md-flex d-grid align-items-center gap-3">
                                                    <button type="submit" class="btn btn-primary px-4">Send Email</button>
                                                    <a href="news_letter.php?restart=1" class="btn btn-light px-4">Restart</a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Sent Emails -->
                        <!-- (Your existing code for displaying sent emails can remain here) -->

                    </div>
                </div><!--end row-->
            </div>
        </div>
        <!--end page wrapper -->
        <!--start overlay-->
        <div class="overlay toggle-icon"></div>
        <!--end overlay-->
        <!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
        <!--End Back To Top Button-->
        <footer class="page-footer">
            <p class="mb-0">Copyright &copy; 2024. All right reserved.</p>
        </footer>
    </div>

    <!--end switcher-->
    <!-- Bootstrap JS -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <!--plugins-->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
    <script src="assets/plugins/metismenu/js/metisMenu.min.js"></script>
    <script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
    <!--app JS-->
    <script src="assets/js/app.js"></script>
</body>

</html>
