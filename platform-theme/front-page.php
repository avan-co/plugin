<?php
/**
 * Front page template.
 *
 * @package PlatformTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main class="mpp-home">
	<section class="mpp-home-hero">
		<div class="mpp-home-container">
			<h1 class="mpp-home-hero__title">پلتفرم چندپنلی مبتنی بر وردپرس</h1>
			<p class="mpp-home-hero__text">زیرساخت یکپارچه برای مدیریت کاربران، نقش‌ها، دسترسی‌ها و پنل‌های کاربری، مدیریتی و ادمین با معماری ماژولار و امن.</p>
			<div class="mpp-home-hero__actions">
				<a class="mpp-btn mpp-btn--primary" href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'login' ) : home_url( '/login' ) ); ?>">ورود</a>
				<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'register' ) : home_url( '/register' ) ); ?>">ثبت‌نام</a>
			</div>
		</div>
	</section>

	<section class="mpp-home-section">
		<div class="mpp-home-container">
			<h2>ویژگی‌های کلیدی</h2>
			<div class="mpp-home-grid">
				<article class="mpp-home-card"><h3>معماری ماژولار</h3><p>افزودن ماژول‌های مستقل بدون تغییر هسته پلتفرم.</p></article>
				<article class="mpp-home-card"><h3>مدیریت نقش و دسترسی</h3><p>ACL پویا با نقش‌ها، مجوزها و محدوده دسترسی.</p></article>
				<article class="mpp-home-card"><h3>پنل‌های چندگانه</h3><p>تجربه جداگانه برای کاربر، مدیر و ادمین.</p></article>
				<article class="mpp-home-card"><h3>پایه امن وردپرس</h3><p>احراز هویت و کاربران بر پایه WordPress Core.</p></article>
			</div>
		</div>
	</section>

	<section class="mpp-home-section mpp-home-section--muted">
		<div class="mpp-home-container">
			<h2>نحوه کار</h2>
			<ol class="mpp-home-steps">
				<li>ثبت‌نام یا ورود به پلتفرم</li>
				<li>دریافت نقش پلتفرمی (مثلاً platform_user)</li>
				<li>دسترسی به پنل متناسب با مجوزها</li>
				<li>مدیریت ACL توسط ادمین پلتفرم</li>
			</ol>
		</div>
	</section>

	<section class="mpp-home-section">
		<div class="mpp-home-container">
			<h2>پنل‌های پلتفرم</h2>
			<div class="mpp-home-grid mpp-home-grid--panels">
				<article class="mpp-home-card"><h3>User</h3><p>داشبورد شخصی، پروفایل و تنظیمات حساب.</p></article>
				<article class="mpp-home-card"><h3>Manager</h3><p>فضای مدیریتی پایه برای تیم و ماژول‌ها.</p></article>
				<article class="mpp-home-card"><h3>Admin</h3><p>مدیریت کاربران، نقش‌ها، مجوزها و ACL.</p></article>
			</div>
		</div>
	</section>

	<section class="mpp-home-section mpp-home-section--muted">
		<div class="mpp-home-container">
			<h2>معماری</h2>
			<div class="mpp-home-arch">
				<div class="mpp-home-arch__item">WordPress Core</div>
				<div class="mpp-home-arch__arrow">↓</div>
				<div class="mpp-home-arch__item">Platform Core</div>
				<div class="mpp-home-arch__arrow">↓</div>
				<div class="mpp-home-arch__item">Platform Modules</div>
			</div>
		</div>
	</section>

	<section class="mpp-home-cta">
		<div class="mpp-home-container">
			<h2>آماده شروع هستید؟</h2>
			<p>همین حالا وارد پلتفرم شوید یا حساب جدید بسازید.</p>
			<div class="mpp-home-hero__actions">
				<a class="mpp-btn mpp-btn--primary" href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'login' ) : home_url( '/login' ) ); ?>">ورود</a>
				<a class="mpp-btn mpp-btn--secondary" href="<?php echo esc_url( function_exists( 'mpp_route_url' ) ? mpp_route_url( 'register' ) : home_url( '/register' ) ); ?>">ثبت‌نام</a>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
