<?php
session_start(); 
include('includes/header.php');
include('includes/navbar.php'); 
?>

<!-- Breadcrumb Area Start -->
<section class="breadcrumb-area extra-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="title extra-padding">
                    FAQ
                </h4>
                <ul class="breadcrumb-list">
                    <li>
                        <a href="index">
                            <i class="fas fa-home"></i>
                            Home
                        </a>
                    </li>
                    <li>
                        <span><i class="fas fa-chevron-right"></i> </span>
                    </li>
                    <li>
                        <a href="faq">FAQ</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<!-- Breadcrumb Area End -->

<!-- Faq Area Start -->
<section class="faq-area2">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="section-heading">
                    <h2 class="title extra-padding">
                        Frequently Asked Questions
                    </h2>
                    <p class="text">
                        Common questions about scanning TeslaTags, claiming rewards, and withdrawing funds on our platform.
                    </p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="content">
                    <div class="accordion" id="tour-faq2">
                        <div class="single-accordion">
                            <div class="accordion-header">
                                <h4 class="title" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    <i class="far fa-user"></i> How do I start using the platform?
                                </h4>
                            </div>
                            <div id="collapseOne" class="collapse show" data-parent="#tour-faq2">
                                <div class="accordion-body">
                                    To start, click the "Sign Up" button on our homepage, fill out the required information, and create your account. Once registered, you can begin scanning TeslaTags to claim rewards.
                                </div>
                            </div>
                        </div>
                        <div class="single-accordion">
                            <div class="accordion-header">
                                <h4 class="title collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    <i class="fas fa-qrcode"></i> How do I scan a TeslaTag?
                                </h4>
                            </div>
                            <div id="collapseTwo" class="collapse" data-parent="#tour-faq2">
                                <div class="accordion-body">
                                    Log in to your account, navigate to the "Scan TeslaTag" section, and use your device to scan the TeslaTag code. Follow the prompts to claim your reward.
                                </div>
                            </div>
                        </div>
                        <div class="single-accordion">
                            <div class="accordion-header">
                                <h4 class="title collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    <i class="fas fa-money-check-alt"></i> How do I withdraw my rewards?
                                </h4>
                            </div>
                            <div id="collapseThree" class="collapse" data-parent="#tour-faq2">
                                <div class="accordion-body">
                                    Go to the "Withdraw" section in your dashboard, enter the amount you wish to withdraw, and select your preferred payment method. Your funds will be transferred instantly.
                                </div>
                            </div>
                        </div>
                        <div class="single-accordion">
                            <div class="accordion-header">
                                <h4 class="title collapsed" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    <i class="fas fa-undo"></i> Are withdrawals refundable?
                                </h4>
                            </div>
                            <div id="collapseFour" class="collapse" data-parent="#tour-faq2">
                                <div class="accordion-body">
                                    Once a withdrawal is processed, it is final. Please ensure all details are correct before confirming your withdrawal request.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="content">
                    <div class="accordion" id="tour-faq3">
                        <div class="single-accordion">
                            <div class="accordion-header">
                                <h4 class="title" data-toggle="collapse" data-target="#collapse5" aria-expanded="true" aria-controls="collapse5">
                                    <i class="fas fa-wallet"></i> What is the minimum withdrawal amount?
                                </h4>
                            </div>
                            <div id="collapse5" class="collapse show" data-parent="#tour-faq3">
                                <div class="accordion-body">
                                    The minimum withdrawal amount is $50. You can withdraw any amount above this threshold, up to a maximum of $10,000 per transaction.
                                </div>
                            </div>
                        </div>
                        <div class="single-accordion">
                            <div class="accordion-header">
                                <h4 class="title collapsed" data-toggle="collapse" data-target="#collapse6" aria-expanded="false" aria-controls="collapse6">
                                    <i class="fas fa-mobile-alt"></i> Is there a mobile app for scanning TeslaTags?
                                </h4>
                            </div>
                            <div id="collapse6" class="collapse" data-parent="#tour-faq3">
                                <div class="accordion-body">
                                    Yes, our mobile app is available for iOS and Android. Download it from the App Store or Google Play to scan TeslaTags and manage your rewards on the go.
                                </div>
                            </div>
                        </div>
                        <div class="single-accordion">
                            <div class="accordion-header">
                                <h4 class="title collapsed" data-toggle="collapse" data-target="#collapse7" aria-expanded="false" aria-controls="collapse7">
                                    <i class="fas fa-history"></i> How do I view my past transactions?
                                </h4>
                            </div>
                            <div id="collapse7" class="collapse" data-parent="#tour-faq3">
                                <div class="accordion-body">
                                    Log in to your account and visit the "Transaction History" section on your dashboard to view all your past TeslaTag scans and withdrawals.
                                </div>
                            </div>
                        </div>
                        <div class="single-accordion">
                            <div class="accordion-header">
                                <h4 class="title collapsed" data-toggle="collapse" data-target="#collapse8" aria-expanded="false" aria-controls="collapse8">
                                    <i class="fas fa-shield-alt"></i> Is the platform secure?
                                </h4>
                            </div>
                            <div id="collapse8" class="collapse" data-parent="#tour-faq3">
                                <div class="accordion-body">
                                    Our platform uses advanced encryption and security protocols to ensure your data and transactions are fully protected.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Faq Area End -->

<?php include('includes/footer.php'); ?>
