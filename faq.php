<?php
$page_title = 'سوالات متداول';
require_once 'includes/header.php';
?>

<main class="container py-5 my-5">
    <div class="section-title text-center mb-5" data-aos="fade-down">
        <h1>سوالات متداول</h1>
        <p class="fs-5 text-muted">پاسخ به برخی از سوالات شما</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="accordion" id="faqAccordion">

                <div class="accordion-item" data-aos="fade-up">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                            چگونه می‌توانم سفارش خود را ثبت کنم؟
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>شما می‌توانید با مراجعه به بخش فروشگاه، محصولات مورد نظر خود را به سبد خرید اضافه کرده و سپس با تکمیل اطلاعات و پرداخت، سفارش خود را نهایی کنید.</p>
                        </div>
                    </div>
                </div>

                <div class="accordion-item" data-aos="fade-up" data-aos-delay="100">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            زمان ارسال سفارش چقدر است؟
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>سفارش‌ها در تهران طی ۲ تا ۳ روز کاری و در سایر شهرها طی ۴ تا ۷ روز کاری از طریق پست پیشتاز ارسال می‌شوند. کد رهگیری پستی پس از ارسال، برای شما پیامک خواهد شد.</p>
                        </div>
                    </div>
                </div>

                <div class="accordion-item" data-aos="fade-up" data-aos-delay="200">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            آیا امکان بازگشت کالا وجود دارد؟
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>بله، در صورت عدم رضایت از محصول یا وجود هرگونه مغایرت، تا ۷ روز پس از دریافت کالا فرصت دارید تا آن را بازگردانید. لطفاً توجه داشته باشید که محصول نباید استفاده شده باشد و بسته‌بندی آن آسیب ندیده باشد. برای هماهنگی با پشتیبانی تماس بگیرید.</p>
                        </div>
                    </div>
                </div>

                <div class="accordion-item" data-aos="fade-up" data-aos-delay="300">
                    <h2 class="accordion-header" id="headingFour">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                            چگونه می‌توانم سفارشم را پیگیری کنم؟
                        </button>
                    </h2>
                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>پس از ارسال سفارش، یک کد رهگیری ۲۴ رقمی از طریق پیامک برای شما ارسال می‌شود. شما می‌توانید با مراجعه به وب‌سایت رسمی پست و وارد کردن این کد، از آخرین وضعیت بسته خود مطلع شوید. همچنین می‌توانید از طریق صفحه "پیگیری سفارش" در سایت ما نیز اقدام کنید.</p>
                        </div>
                    </div>
                </div>

                <div class="accordion-item" data-aos="fade-up" data-aos-delay="400">
                    <h2 class="accordion-header" id="headingFive">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                            آیا محصولات شما دارای ضمانت هستند؟
                        </button>
                    </h2>
                    <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>بله، تمامی محصولات چرم ما دارای ۶ ماه ضمانت کیفیت دوخت و یراق‌آلات هستند. این ضمانت شامل آسیب‌های ناشی از استفاده نادرست نمی‌شود.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
