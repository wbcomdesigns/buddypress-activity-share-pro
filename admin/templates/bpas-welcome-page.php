<?php
/**
 * Welcome Page Template for BuddyPress Activity Share Pro
 *
 * This template displays the welcome page content for the plugin.
 * Updated for the independent menu system without wbcom wrapper.
 * Enhanced with modern styling and comprehensive information.
 *
 * @link       http://wbcomdesigns.com
 * @since      1.0.0
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/admin/templates
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="bp-share-welcome-content">
	<div class="bp-share-welcome-header">
		<h2><?php esc_html_e( 'Welcome to BuddyPress Activity Share Pro', 'buddypress-share' ); ?></h2>
		<p class="bp-share-welcome-description">
			<?php esc_html_e( 'Transform your BuddyPress community with powerful sharing capabilities. Enable your members to share activities across social media platforms and within your community, boosting engagement and extending your reach.', 'buddypress-share' ); ?>
		</p>
	</div>

	<div class="bp-share-stats-grid">
		<div class="bp-share-stat-card">
			<div class="bp-share-stat-icon">
				<span class="dashicons dashicons-share"></span>
			</div>
			<div class="bp-share-stat-info">
				<h3><?php echo esc_html( count( get_site_option( 'bp_share_services', array() ) ) ); ?></h3>
				<p><?php esc_html_e( 'Social Services Enabled', 'buddypress-share' ); ?></p>
			</div>
		</div>

		<div class="bp-share-stat-card">
			<div class="bp-share-stat-icon">
				<span class="dashicons dashicons-networking"></span>
			</div>
			<div class="bp-share-stat-info">
				<h3><?php echo get_site_option( 'bp_share_services_enable' ) ? esc_html__( 'Active', 'buddypress-share' ) : esc_html__( 'Inactive', 'buddypress-share' ); ?></h3>
				<p><?php esc_html_e( 'Sharing Status', 'buddypress-share' ); ?></p>
			</div>
		</div>

		<div class="bp-share-stat-card">
			<div class="bp-share-stat-icon">
				<span class="dashicons dashicons-admin-users"></span>
			</div>
			<div class="bp-share-stat-info">
				<h3><?php echo get_site_option( 'bp_share_services_logout_enable' ) ? esc_html__( 'Yes', 'buddypress-share' ) : esc_html__( 'No', 'buddypress-share' ); ?></h3>
				<p><?php esc_html_e( 'Guest Sharing', 'buddypress-share' ); ?></p>
			</div>
		</div>
	</div>

	<div class="bp-share-features-showcase">
		<h3><?php esc_html_e( 'Powerful Features', 'buddypress-share' ); ?></h3>
		<div class="bp-share-features-grid">
			<div class="bp-share-feature-card">
				<div class="bp-share-feature-icon">
					<span class="dashicons dashicons-share"></span>
				</div>
				<div class="bp-share-feature-content">
					<h4><?php esc_html_e( 'Social Media Integration', 'buddypress-share' ); ?></h4>
					<p><?php esc_html_e( 'Share activities to Facebook, Twitter, LinkedIn, WhatsApp, Pinterest, and more. Expand your community\'s reach across social platforms.', 'buddypress-share' ); ?></p>
					<ul class="bp-share-feature-list">
						<li><?php esc_html_e( '11+ Social Platforms', 'buddypress-share' ); ?></li>
						<li><?php esc_html_e( 'One-Click Sharing', 'buddypress-share' ); ?></li>
						<li><?php esc_html_e( 'Custom Share URLs', 'buddypress-share' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="bp-share-feature-card">
				<div class="bp-share-feature-icon">
					<span class="dashicons dashicons-groups"></span>
				</div>
				<div class="bp-share-feature-content">
					<h4><?php esc_html_e( 'Community Sharing', 'buddypress-share' ); ?></h4>
					<p><?php esc_html_e( 'Enable internal sharing within your BuddyPress community. Share to profiles, groups, friends, and private messages.', 'buddypress-share' ); ?></p>
					<ul class="bp-share-feature-list">
						<li><?php esc_html_e( 'Share to Groups', 'buddypress-share' ); ?></li>
						<li><?php esc_html_e( 'Share with Friends', 'buddypress-share' ); ?></li>
						<li><?php esc_html_e( 'Private Messages', 'buddypress-share' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="bp-share-feature-card">
				<div class="bp-share-feature-icon">
					<span class="dashicons dashicons-admin-customizer"></span>
				</div>
				<div class="bp-share-feature-content">
					<h4><?php esc_html_e( 'Customizable Design', 'buddypress-share' ); ?></h4>
					<p><?php esc_html_e( 'Choose from multiple icon styles and customize colors to match your brand. Create a seamless user experience.', 'buddypress-share' ); ?></p>
					<ul class="bp-share-feature-list">
						<li><?php esc_html_e( '4 Icon Styles', 'buddypress-share' ); ?></li>
						<li><?php esc_html_e( 'Custom Colors', 'buddypress-share' ); ?></li>
						<li><?php esc_html_e( 'Live Preview', 'buddypress-share' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="bp-share-feature-card">
				<div class="bp-share-feature-icon">
					<span class="dashicons dashicons-performance"></span>
				</div>
				<div class="bp-share-feature-content">
					<h4><?php esc_html_e( 'Performance Optimized', 'buddypress-share' ); ?></h4>
					<p><?php esc_html_e( 'Built for speed and scalability. Optimized for large communities with caching, lazy loading, and minimal database queries.', 'buddypress-share' ); ?></p>
					<ul class="bp-share-feature-list">
						<li><?php esc_html_e( 'Smart Caching', 'buddypress-share' ); ?></li>
						<li><?php esc_html_e( 'Lazy Loading', 'buddypress-share' ); ?></li>
						<li><?php esc_html_e( 'Minimal Queries', 'buddypress-share' ); ?></li>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<div class="bp-share-quick-setup">
		<div class="bp-share-setup-content">
			<div class="bp-share-setup-info">
				<h3><?php esc_html_e( 'Quick Setup Guide', 'buddypress-share' ); ?></h3>
				<p><?php esc_html_e( 'Get your sharing system up and running in just a few steps:', 'buddypress-share' ); ?></p>
				<ol class="bp-share-setup-steps">
					<li><?php esc_html_e( 'Enable social sharing in General Settings', 'buddypress-share' ); ?></li>
					<li><?php esc_html_e( 'Configure which social services to display', 'buddypress-share' ); ?></li>
					<li><?php esc_html_e( 'Customize your sharing options in Share Settings', 'buddypress-share' ); ?></li>
					<li><?php esc_html_e( 'Style your icons to match your brand', 'buddypress-share' ); ?></li>
				</ol>
			</div>
			<div class="bp-share-setup-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=buddypress-share&tab=bpas_general_settings' ) ); ?>" class="bp-share-cta-button primary">
					<span class="dashicons dashicons-admin-generic"></span>
					<?php esc_html_e( 'Start Configuration', 'buddypress-share' ); ?>
				</a>
			</div>
		</div>
	</div>

	<div class="bp-share-help-resources">
		<h3><?php esc_html_e( 'Help & Resources', 'buddypress-share' ); ?></h3>
		<div class="bp-share-resources-grid">
			<div class="bp-share-resource-card">
				<div class="bp-share-resource-icon">
					<span class="dashicons dashicons-book"></span>
				</div>
				<div class="bp-share-resource-content">
					<h4><?php esc_html_e( 'Documentation', 'buddypress-share' ); ?></h4>
					<p><?php esc_html_e( 'Comprehensive guides, tutorials, and examples to help you make the most of the plugin.', 'buddypress-share' ); ?></p>
					<a href="https://docs.wbcomdesigns.com/doc_category/buddypress-activity-social-share/" target="_blank" class="bp-share-resource-link">
						<?php esc_html_e( 'Browse Documentation', 'buddypress-share' ); ?>
						<span class="dashicons dashicons-external"></span>
					</a>
				</div>
			</div>

			<div class="bp-share-resource-card">
				<div class="bp-share-resource-icon">
					<span class="dashicons dashicons-sos"></span>
				</div>
				<div class="bp-share-resource-content">
					<h4><?php esc_html_e( 'Support Center', 'buddypress-share' ); ?></h4>
					<p><?php esc_html_e( 'Get personalized help from our expert support team. We\'re here to help you succeed.', 'buddypress-share' ); ?></p>
					<a href="https://wbcomdesigns.com/support/" target="_blank" class="bp-share-resource-link">
						<?php esc_html_e( 'Get Support', 'buddypress-share' ); ?>
						<span class="dashicons dashicons-external"></span>
					</a>
				</div>
			</div>

			<div class="bp-share-resource-card">
				<div class="bp-share-resource-icon">
					<span class="dashicons dashicons-admin-comments"></span>
				</div>
				<div class="bp-share-resource-content">
					<h4><?php esc_html_e( 'Community Forum', 'buddypress-share' ); ?></h4>
					<p><?php esc_html_e( 'Connect with other users, share tips, and get answers from the community.', 'buddypress-share' ); ?></p>
					<a href="https://wbcomdesigns.com/submit-review/" target="_blank" class="bp-share-resource-link">
						<?php esc_html_e( 'Join Discussion', 'buddypress-share' ); ?>
						<span class="dashicons dashicons-external"></span>
					</a>
				</div>
			</div>
		</div>
	</div>

	<div class="bp-share-pro-tips">
		<h3><?php esc_html_e( '💡 Pro Tips', 'buddypress-share' ); ?></h3>
		<div class="bp-share-tips-grid">
			<div class="bp-share-tip-card">
				<h4><?php esc_html_e( 'Boost Engagement', 'buddypress-share' ); ?></h4>
				<p><?php esc_html_e( 'Enable guest sharing to allow non-members to share your content, expanding your reach beyond your community.', 'buddypress-share' ); ?></p>
			</div>
			<div class="bp-share-tip-card">
				<h4><?php esc_html_e( 'Brand Consistency', 'buddypress-share' ); ?></h4>
				<p><?php esc_html_e( 'Customize icon colors to match your brand palette for a professional, cohesive look across your site.', 'buddypress-share' ); ?></p>
			</div>
			<div class="bp-share-tip-card">
				<h4><?php esc_html_e( 'Privacy Control', 'buddypress-share' ); ?></h4>
				<p><?php esc_html_e( 'Use Share Settings to control what types of content can be shared, maintaining privacy where needed.', 'buddypress-share' ); ?></p>
			</div>
		</div>
	</div>
</div>

<style>
/* Welcome Page Enhanced Styles */
.bp-share-welcome-content {
	padding: 0;
}

.bp-share-welcome-header {
	text-align: center;
	margin-bottom: 40px;
}

.bp-share-welcome-header h2 {
	font-size: 32px;
	color: #333;
	margin-bottom: 16px;
	font-weight: 600;
}

.bp-share-welcome-description {
	font-size: 18px;
	line-height: 1.6;
	color: #666;
	max-width: 800px;
	margin: 0 auto;
}

.bp-share-stats-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 20px;
	margin-bottom: 50px;
}

.bp-share-stat-card {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: #fff;
	padding: 24px;
	border-radius: 12px;
	display: flex;
	align-items: center;
	gap: 16px;
	box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
}

.bp-share-stat-icon {
	width: 48px;
	height: 48px;
	background: rgba(255, 255, 255, 0.2);
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}

.bp-share-stat-icon .dashicons {
	font-size: 24px;
	color: #fff;
}

.bp-share-stat-info h3 {
	margin: 0 0 4px 0;
	font-size: 24px;
	font-weight: 700;
}

.bp-share-stat-info p {
	margin: 0;
	font-size: 14px;
	opacity: 0.9;
}

.bp-share-features-showcase {
	margin-bottom: 50px;
}

.bp-share-features-showcase h3 {
	text-align: center;
	font-size: 28px;
	color: #333;
	margin-bottom: 40px;
	font-weight: 600;
}

.bp-share-features-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 30px;
}

.bp-share-feature-card {
	background: #fff;
	border: 1px solid #e1e5e9;
	border-radius: 12px;
	padding: 30px;
	transition: all 0.3s ease;
	box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.bp-share-feature-card:hover {
	transform: translateY(-5px);
	box-shadow: 0 8px 25px rgba(0,0,0,0.1);
	border-color: #667eea;
}

.bp-share-feature-icon {
	width: 64px;
	height: 64px;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	border-radius: 12px;
	display: flex;
	align-items: center;
	justify-content: center;
	margin-bottom: 20px;
}

.bp-share-feature-icon .dashicons {
	font-size: 28px;
	color: #fff;
}

.bp-share-feature-content h4 {
	font-size: 20px;
	color: #333;
	margin: 0 0 12px 0;
	font-weight: 600;
}

.bp-share-feature-content p {
	color: #666;
	line-height: 1.6;
	margin: 0 0 16px 0;
}

.bp-share-feature-list {
	list-style: none;
	padding: 0;
	margin: 0;
}

.bp-share-feature-list li {
	padding: 4px 0;
	color: #667eea;
	font-weight: 500;
	position: relative;
	padding-left: 20px;
}

.bp-share-feature-list li:before {
	content: "✓";
	position: absolute;
	left: 0;
	color: #28a745;
	font-weight: bold;
}

.bp-share-quick-setup {
	background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
	border-radius: 12px;
	padding: 40px;
	margin-bottom: 50px;
}

.bp-share-setup-content {
	display: grid;
	grid-template-columns: 2fr 1fr;
	gap: 40px;
	align-items: center;
}

.bp-share-setup-info h3 {
	font-size: 24px;
	color: #333;
	margin: 0 0 16px 0;
	font-weight: 600;
}

.bp-share-setup-info p {
	color: #666;
	line-height: 1.6;
	margin: 0 0 20px 0;
}

.bp-share-setup-steps {
	padding-left: 20px;
	color: #555;
	line-height: 1.8;
}

.bp-share-setup-steps li {
	margin-bottom: 8px;
}

.bp-share-setup-actions {
	text-align: center;
}

.bp-share-cta-button {
	display: inline-flex;
	align-items: center;
	gap: 10px;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: #fff;
	text-decoration: none;
	padding: 16px 32px;
	border-radius: 8px;
	font-weight: 600;
	font-size: 16px;
	transition: all 0.3s ease;
	box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.bp-share-cta-button:hover {
	background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
	transform: translateY(-2px);
	box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
	color: #fff;
}

.bp-share-help-resources {
	margin-bottom: 50px;
}

.bp-share-help-resources h3 {
	text-align: center;
	font-size: 24px;
	color: #333;
	margin-bottom: 30px;
	font-weight: 600;
}

.bp-share-resources-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
	gap: 24px;
}

.bp-share-resource-card {
	background: #fff;
	border: 1px solid #e1e5e9;
	border-radius: 8px;
	padding: 24px;
	text-align: center;
	transition: all 0.3s ease;
}

.bp-share-resource-card:hover {
	transform: translateY(-3px);
	box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}

.bp-share-resource-icon {
	width: 56px;
	height: 56px;
	background: #f8f9fa;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	margin: 0 auto 16px;
}

.bp-share-resource-icon .dashicons {
	font-size: 24px;
	color: #667eea;
}

.bp-share-resource-content h4 {
	font-size: 18px;
	color: #333;
	margin: 0 0 12px 0;
	font-weight: 600;
}

.bp-share-resource-content p {
	color: #666;
	line-height: 1.5;
	margin: 0 0 16px 0;
	font-size: 14px;
}

.bp-share-resource-link {
	color: #667eea;
	text-decoration: none;
	font-weight: 500;
	display: inline-flex;
	align-items: center;
	gap: 6px;
	transition: color 0.3s ease;
}

.bp-share-resource-link:hover {
	color: #5a6fd8;
}

.bp-share-resource-link .dashicons {
	font-size: 14px;
}

.bp-share-pro-tips h3 {
	text-align: center;
	font-size: 24px;
	color: #333;
	margin-bottom: 30px;
	font-weight: 600;
}

.bp-share-tips-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 20px;
}

.bp-share-tip-card {
	background: linear-gradient(135deg, #fff5e6 0%, #ffe0b3 100%);
	border: 1px solid #ffc966;
	border-radius: 8px;
	padding: 20px;
}

.bp-share-tip-card h4 {
	font-size: 16px;
	color: #cc8500;
	margin: 0 0 8px 0;
	font-weight: 600;
}

.bp-share-tip-card p {
	color: #8b6914;
	line-height: 1.5;
	margin: 0;
	font-size: 14px;
}

/* Responsive Design */
@media (max-width: 768px) {
	.bp-share-setup-content {
		grid-template-columns: 1fr;
		gap: 30px;
	}
	
	.bp-share-stats-grid {
		grid-template-columns: 1fr;
	}
	
	.bp-share-features-grid {
		grid-template-columns: 1fr;
	}
	
	.bp-share-welcome-header h2 {
		font-size: 24px;
	}
	
	.bp-share-welcome-description {
		font-size: 16px;
	}
}
</style>