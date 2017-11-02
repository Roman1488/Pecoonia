

<footer id="footer" ng-controller="FooterController">
    <section class="section swatch-black  section-text-no-shadow section-inner-no-shadow section-normal section-opaque" id="module-footer">
        <div class="container">
            <div class="row vertical-top">
                <div class="col-md-12     text-default small-screen-default">
                    <div class="row ">
                        <div class="col-md-3 text-default small-screen-default">
                            <div class="element-no-top element-no-bottom" data-os-animation="none" data-os-animation-delay="0s">
                                <ul class="social-icons social-lg social-simple social-rect ">
                                    <li class="facebook">
                                        <a href="https://www.facebook.com/pecoonia" target="_self"><i class="fa fa-facebook"></i></a>
                                    </li>
                                    <li>
                                        <a href="https://www.twitter.com/pecoonia" target="_self"><i class="fa fa-twitter"></i></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-9 text-default small-screen-default">
                            <div class="footer-top-links element-no-top element-no-bottom" data-os-animation="none" data-os-animation-delay="0s">
                                <ul>
                                    <li><a href="/"><img class="alignnone wp-image-496 size-full" src="images/footer-logo.png" alt="logo"></a></li>
                                    <li><a href="#" ng-click="openAboutUsModal()">About Us</a></li>
                                    <li class="email-btn"  ng-click="openFeedbackModal()"><i class="fa fa-envelope"></i>Contact Us</li>
                                </ul>
                            </div>
                            <div class="footer-bottom-links element-no-top element-no-bottom" data-os-animation="none" data-os-animation-delay="0s">
                                <ul>
                                    <li><a href="#" ng-click="openTermsOfUseModal()">Terms of Use</a></li>
                                    <li><a href="#" ng-click="openPrivacyModal()">Privacy</a></li>
                                    <li>©<?php echo date('Y') ?> All Rights Reserved.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{--<div class="container">--}}

        {{--<!-- Footer Widgets--}}
        {{--============================================= -->--}}
        {{--<div class="footer-widgets-wrap clearfix">--}}

            {{--<div class="col_two_third">--}}

                {{--<div class="col_one_third">--}}

                    {{--<div class="widget clearfix">--}}

                        {{--<img src="images/footer-widget-logo.png" alt="" class="footer-logo">--}}

                        {{--<p>We believe in <strong>Simple</strong>, <strong>Creative</strong> &amp; <strong>Flexible</strong> Design Standards.</p>--}}

                        {{--<div style="background: url('images/world-map.png') no-repeat center center; background-size: 100%;">--}}
                            {{--<address>--}}
                                {{--<strong>Headquarters:</strong><br>--}}
                                {{--795 Folsom Ave, Suite 600<br>--}}
                                {{--San Francisco, CA 94107<br>--}}
                            {{--</address>--}}
                            {{--<abbr title="Phone Number"><strong>Phone:</strong></abbr> (91) 8547 632521<br>--}}
                            {{--<abbr title="Fax"><strong>Fax:</strong></abbr> (91) 11 4752 1433<br>--}}
                            {{--<abbr title="Email Address"><strong>Email:</strong></abbr> info@canvas.com--}}
                        {{--</div>--}}

                    {{--</div>--}}

                {{--</div>--}}

                {{--<div class="col_one_third">--}}

                    {{--<div class="widget widget_links clearfix">--}}

                        {{--<h4>Blogroll</h4>--}}

                        {{--<ul>--}}
                            {{--<li><a href="http://codex.wordpress.org/">Documentation</a></li>--}}
                            {{--<li><a href="http://wordpress.org/support/forum/requests-and-feedback">Feedback</a></li>--}}
                            {{--<li><a href="http://wordpress.org/extend/plugins/">Plugins</a></li>--}}
                            {{--<li><a href="http://wordpress.org/support/">Support Forums</a></li>--}}
                            {{--<li><a href="http://wordpress.org/extend/themes/">Themes</a></li>--}}
                            {{--<li><a href="http://wordpress.org/news/">WordPress Blog</a></li>--}}
                            {{--<li><a href="http://planet.wordpress.org/">WordPress Planet</a></li>--}}
                        {{--</ul>--}}

                    {{--</div>--}}

                {{--</div>--}}

                {{--<div class="col_one_third col_last">--}}

                    {{--<div class="widget clearfix">--}}
                        {{--<h4>Recent Posts</h4>--}}

                        {{--<div id="post-list-footer">--}}
                            {{--<div class="spost clearfix">--}}
                                {{--<div class="entry-c">--}}
                                    {{--<div class="entry-title">--}}
                                        {{--<h4><a href="#">Lorem ipsum dolor sit amet, consectetur</a></h4>--}}
                                    {{--</div>--}}
                                    {{--<ul class="entry-meta">--}}
                                        {{--<li>10th July 2014</li>--}}
                                    {{--</ul>--}}
                                {{--</div>--}}
                            {{--</div>--}}

                            {{--<div class="spost clearfix">--}}
                                {{--<div class="entry-c">--}}
                                    {{--<div class="entry-title">--}}
                                        {{--<h4><a href="#">Elit Assumenda vel amet dolorum quasi</a></h4>--}}
                                    {{--</div>--}}
                                    {{--<ul class="entry-meta">--}}
                                        {{--<li>10th July 2014</li>--}}
                                    {{--</ul>--}}
                                {{--</div>--}}
                            {{--</div>--}}

                            {{--<div class="spost clearfix">--}}
                                {{--<div class="entry-c">--}}
                                    {{--<div class="entry-title">--}}
                                        {{--<h4><a href="#">Debitis nihil placeat, illum est nisi</a></h4>--}}
                                    {{--</div>--}}
                                    {{--<ul class="entry-meta">--}}
                                        {{--<li>10th July 2014</li>--}}
                                    {{--</ul>--}}
                                {{--</div>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    {{--</div>--}}

                {{--</div>--}}

            {{--</div>--}}

            {{--<div class="col_one_third col_last">--}}

                {{--<div class="widget clearfix" style="margin-bottom: -20px;">--}}

                    {{--<div class="row">--}}

                        {{--<div class="col-md-6 bottommargin-sm">--}}
                            {{--<div class="counter counter-small"><span data-from="50" data-to="15065421" data-refresh-interval="80" data-speed="3000" data-comma="true"></span></div>--}}
                            {{--<h5 class="nobottommargin">Total Downloads</h5>--}}
                        {{--</div>--}}

                        {{--<div class="col-md-6 bottommargin-sm">--}}
                            {{--<div class="counter counter-small"><span data-from="100" data-to="18465" data-refresh-interval="50" data-speed="2000" data-comma="true"></span></div>--}}
                            {{--<h5 class="nobottommargin">Clients</h5>--}}
                        {{--</div>--}}

                    {{--</div>--}}

                {{--</div>--}}

                {{--<div class="widget subscribe-widget clearfix">--}}
                    {{--<h5><strong>Subscribe</strong> to Our Newsletter to get Important News, Amazing Offers &amp; Inside Scoops:</h5>--}}
                    {{--<div class="widget-subscribe-form-result"></div>--}}
                    {{--<form id="widget-subscribe-form" action="include/subscribe.php" role="form" method="post" class="nobottommargin">--}}
                        {{--<div class="input-group divcenter">--}}
                            {{--<span class="input-group-addon"><i class="icon-email2"></i></span>--}}
                            {{--<input type="email" id="widget-subscribe-form-email" name="widget-subscribe-form-email" class="form-control required email" placeholder="Enter your Email">--}}
                            {{--<span class="input-group-btn">--}}
										{{--<button class="btn btn-success" type="submit">Subscribe</button>--}}
									{{--</span>--}}
                        {{--</div>--}}
                    {{--</form>--}}
                {{--</div>--}}

                {{--<div class="widget clearfix" style="margin-bottom: -20px;">--}}

                    {{--<div class="row">--}}

                        {{--<div class="col-md-6 clearfix bottommargin-sm">--}}
                            {{--<a href="#" class="social-icon si-dark si-colored si-facebook nobottommargin" style="margin-right: 10px;">--}}
                                {{--<i class="icon-facebook"></i>--}}
                                {{--<i class="icon-facebook"></i>--}}
                            {{--</a>--}}
                            {{--<a href="#"><small style="display: block; margin-top: 3px;"><strong>Like us</strong><br>on Facebook</small></a>--}}
                        {{--</div>--}}
                        {{--<div class="col-md-6 clearfix">--}}
                            {{--<a href="#" class="social-icon si-dark si-colored si-rss nobottommargin" style="margin-right: 10px;">--}}
                                {{--<i class="icon-rss"></i>--}}
                                {{--<i class="icon-rss"></i>--}}
                            {{--</a>--}}
                            {{--<a href="#"><small style="display: block; margin-top: 3px;"><strong>Subscribe</strong><br>to RSS Feeds</small></a>--}}
                        {{--</div>--}}

                    {{--</div>--}}

                {{--</div>--}}

            {{--</div>--}}

        {{--</div><!-- .footer-widgets-wrap end -->--}}

    {{--</div>--}}

    <!-- Copyrights
    ============================================= -->
    <!-- <div id="copyrights">

        <div class="container clearfix">

            <div class="col_half">
                Copyrights &copy; {{ date('Y') }} All Rights Reserved by Pecoonia Inc.<br>
                {{--<div class="copyright-links"><a href="#">Terms of Use</a> / <a href="#">Privacy Policy</a></div>--}}
            </div>

            {{--<div class="col_half col_last tright">--}}
                {{--<div class="fright clearfix">--}}
                    {{--<a href="#" class="social-icon si-small si-borderless si-facebook">--}}
                        {{--<i class="icon-facebook"></i>--}}
                        {{--<i class="icon-facebook"></i>--}}
                    {{--</a>--}}

                    {{--<a href="#" class="social-icon si-small si-borderless si-twitter">--}}
                        {{--<i class="icon-twitter"></i>--}}
                        {{--<i class="icon-twitter"></i>--}}
                    {{--</a>--}}

                    {{--<a href="#" class="social-icon si-small si-borderless si-gplus">--}}
                        {{--<i class="icon-gplus"></i>--}}
                        {{--<i class="icon-gplus"></i>--}}
                    {{--</a>--}}

                    {{--<a href="#" class="social-icon si-small si-borderless si-pinterest">--}}
                        {{--<i class="icon-pinterest"></i>--}}
                        {{--<i class="icon-pinterest"></i>--}}
                    {{--</a>--}}

                    {{--<a href="#" class="social-icon si-small si-borderless si-vimeo">--}}
                        {{--<i class="icon-vimeo"></i>--}}
                        {{--<i class="icon-vimeo"></i>--}}
                    {{--</a>--}}

                    {{--<a href="#" class="social-icon si-small si-borderless si-github">--}}
                        {{--<i class="icon-github"></i>--}}
                        {{--<i class="icon-github"></i>--}}
                    {{--</a>--}}

                    {{--<a href="#" class="social-icon si-small si-borderless si-yahoo">--}}
                        {{--<i class="icon-yahoo"></i>--}}
                        {{--<i class="icon-yahoo"></i>--}}
                    {{--</a>--}}

                    {{--<a href="#" class="social-icon si-small si-borderless si-linkedin">--}}
                        {{--<i class="icon-linkedin"></i>--}}
                        {{--<i class="icon-linkedin"></i>--}}
                    {{--</a>--}}
                {{--</div>--}}

                {{--<div class="clear"></div>--}}

                {{--<i class="icon-envelope2"></i> info@canvas.com <span class="middot">&middot;</span> <i class="icon-headphones"></i> +91-11-6541-6369 <span class="middot">&middot;</span> <i class="icon-skype2"></i> CanvasOnSkype--}}
            {{--</div>--}}

        </div>

    </div>#copyrights end -->

    <div id="feedback">
        <div id="feedback-btn" ng-click="openFeedbackModal()">
            FEEDBACK
        </div>
        <div class="contact-form-wrap">
            <div class="contact-form-inner">
                <i class="close-form" ng-click="closeFeedbackModal()">
                    <img src="images/ios-close-outline.png">
                </i>
                <div role="form" lang="en-US" dir="ltr">
                    <div class="screen-reader-response"></div>
                    <form ng-submit="sendFeedback()" name="feedbackForm" method="post" class="form">
                        <h3>Contact Us</h3>
                        <p ng-class="{ 'has-error' : feedbackForm.name.$invalid && !feedbackForm.name.$pristine }">
                            <label> Your Name (required)<br>
                                <span class="form-control-wrap your-name">
                                    <input type="text" name="name" value="" size="40" class="form-control" required></span>
                            </label>
                            <span ng-show="feedbackForm.name.$invalid && !feedbackForm.name.$pristine" class="help-block">The field is required.</span>
                        </p>
                        <p>
                            <label> Your Email (required)<br>
                                <span class="form-control-wrap your-email">
                                    <input type="email" name="email" value="" size="40" class="form-control email" required>
                                </span>
                            </label>
                            <span ng-show="feedbackForm.email.$invalid && !feedbackForm.email.$pristine" class="help-block">The field is required.</span>
                        </p>
                        <p>
                            <label> Subject (required)<br>
                                <span class="form-control-wrap your-subject">
                                    <input type="text" name="subject" value="" size="40" class="form-control" required>
                                </span>
                            </label>
                            <span ng-show="feedbackForm.subject.$invalid && !feedbackForm.subject.$pristine" class="help-block">The field is required.</span>
                        </p>
                        <p>
                            <label> Your Message (required)<br>
                                <span class="form-control-wrap your-message">
                                    <textarea name="message" cols="40" rows="10" class="form-control textarea" required></textarea>
                                </span>
                            </label>
                            <span ng-show="feedbackForm.message.$invalid && !feedbackForm.message.$pristine" class="help-block">The field is required.</span>
                        </p>
                        <p>
                            <input type="submit" value="Send" class="form-control submit">
                        </p>
                    </form>
                </div>
                <div class="form-success-send">
                    <img src="images/form-send.png">
                    <p>Your message has been sent. Thank you for contacting us. We will respond as fast as possible </p>
                </div>
            </div>
        </div>
    </div><!-- #feedback -->

    <div id="aboutus_modal" class="gen-modal-wrap">
        <div class="contact-form-inner">
            <i class="close-form" ng-click="closeGenModal()">
                <img src="images/ios-close-outline.png">
            </i>
            <div id='about_us'></div>
        </div>
    </div>
    <div id="termsofuse_modal" class="gen-modal-wrap">
        <div class="contact-form-inner">
            <i class="close-form" ng-click="closeGenModal()">
                <img src="images/ios-close-outline.png">
            </i>
            <div id='terms_of_use'></div>
        </div>
    </div>
    <div id="privacy_modal" class="gen-modal-wrap">
        <div class="contact-form-inner">
            <i class="close-form" ng-click="closeGenModal()">
                <img src="images/ios-close-outline.png">
            </i>
            <div id='privacy'></div>
        </div>
    </div>

</footer><!-- #footer end -->