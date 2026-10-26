<?php
include_once "./partials/header.php";
include_once "./partials/hero.php";
include_once "./partials/categories.php";
include_once "./partials/discovery.php";
include_once "./partials/product.php";
include_once "./partials/setup-preview.php";
include_once "./partials/compatibility-preview.php";
include_once "./partials/new-product.php";
include_once "./partials/value-proposition.php";
include_once "./partials/buying-guide.php";
include_once "./partials/cta.php";
include_once "./partials/smart-bundle.php"; 
?>
<section
    class="home-commerce-section"
    aria-labelledby="home-collections-title">

    <div class="container">

        <h2 id="home-collections-title">
            مجموعه‌های منتخب
        </h2>

        <div
            data-home-collections
            class="home-commerce-grid">
        </div>

    </div>

</section>

<section
    class="home-commerce-section"
    aria-labelledby="home-sliders-title">

    <div class="container">

        <h2 id="home-sliders-title">
            پیشنهادهای ویژه
        </h2>

        <div
            data-home-sliders
            class="home-commerce-grid">
        </div>

    </div>

</section>
<?php include_once "./partials/footer.php"; ?>