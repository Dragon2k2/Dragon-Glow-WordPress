<?php
/**
 * Dragon Glow — Privacy Policy data
 *
 * Single source of truth cho trang Privacy Policy: hero, TOC list, body sections.
 * Mỗi phần có apply_filters() để theme con / plugin mở rộng mà không phải edit
 * template.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hero — title + last updated + effective date + intro.
 *
 * @return array{title: string, last_updated: string, effective_date: string, intro: string}
 */
function dg_privacy_policy_hero_data(): array {
	$data = array(
		'title'          => __( 'Privacy Policy', 'dragon-glow' ),
		'last_updated'   => __( '1 June 2026', 'dragon-glow' ),
		'effective_date' => __( '1 June 2026', 'dragon-glow' ),
		'intro'          => __( 'Welcome to Dragon Glow. This Privacy Policy outlines how we collect, use, disclose, and safeguard your information when you visit our website, use our products, or engage with our services. We are committed to protecting your personal data and respecting your privacy in accordance with global data protection standards, including the GDPR and CCPA.', 'dragon-glow' ),
	);

	/** This filter is documented in template-parts/privacy-policy/data-privacy-policy.php */
	return (array) apply_filters( 'dg_privacy_policy_hero_data', $data );
}

/**
 * Trả về danh sách section của trang Privacy Policy.
 *
 * Mỗi section có key: id, num, title, body (HTML đã escape). Body lưu thẳng
 * HTML đã chuẩn bị sẵn (link mailto đã được allow trong data, không phải
 * user-submitted content).
 *
 * @return array<int, array{id: string, num: string, title: string, body: string}>
 */
function dg_privacy_policy_sections_data(): array {
	// Helper for plain-text paragraph.
	$p = static function ( string $text ): string {
		return '<p>' . esc_html( $text ) . '</p>';
	};

	$sections = array(
		array(
			'id'    => 'section-1',
			'num'   => '01',
			'title' => __( 'Who we are', 'dragon-glow' ),
			'body'  => $p( __( 'Dragon Glow, Inc. is the controller of your personal information. We are a corporation registered in the State of Delaware, with our principal place of business at 215 Mercer Street, Suite 400, New York, NY 10012, United States.', 'dragon-glow' ) )
				. '<p>' . sprintf( wp_kses( __( 'For data protection questions, contact our privacy team at <a href="%1$s">%2$s</a>. Our Data Protection Officer can be reached at <a href="%3$s">%4$s</a>.', 'dragon-glow' ), array( 'a' => array( 'href' => array() ) ) ), 'mailto:privacy@dragonglows.page.gd', 'privacy@dragonglows.page.gd', 'mailto:dpo@dragonglows.page.gd', 'dpo@dragonglows.page.gd' ) . '</p>'
				. '<p>' . sprintf( wp_kses( __( 'Our EU representative under Article 27 GDPR is Glow Compliance Ltd, 25 Merrion Square, Dublin 2, D02 PX67, Ireland (<a href="%1$s">%2$s</a>). Our UK representative is Glow Compliance UK Ltd, 40 Berkeley Square, London W1J 5AL, United Kingdom (<a href="%3$s">%4$s</a>).', 'dragon-glow' ), array( 'a' => array( 'href' => array() ) ) ), 'mailto:eu-rep@dragonglows.page.gd', 'eu-rep@dragonglows.page.gd', 'mailto:uk-rep@dragonglows.page.gd', 'uk-rep@dragonglows.page.gd' ) . '</p>',
		),
		array(
			'id'    => 'section-2',
			'num'   => '02',
			'title' => __( 'Scope of this Policy', 'dragon-glow' ),
			'body'  => $p( __( 'This Policy applies to personal information we process about visitors, account holders, and customers through our website and related services. It does not apply to third-party websites or services we link to, which are governed by their own privacy policies.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-3',
			'num'   => '03',
			'title' => __( 'Information we collect', 'dragon-glow' ),
			'body'  => '<p><strong>' . esc_html__( 'Information you provide to us:', 'dragon-glow' ) . '</strong></p>'
				. '<ul><li><strong>' . esc_html__( 'Identity and contact data:', 'dragon-glow' ) . '</strong> ' . esc_html__( 'name, email address, phone number, shipping and billing address.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Account data:', 'dragon-glow' ) . '</strong> ' . esc_html__( 'username, password (stored hashed), preferences.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Order and transaction data:', 'dragon-glow' ) . '</strong> ' . esc_html__( 'products purchased, order history, returns, the last four digits and type of payment card (full card details are processed by our payment providers, not stored by us).', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Communications:', 'dragon-glow' ) . '</strong> ' . esc_html__( 'messages you send to our concierge or privacy team, reviews, and survey responses.', 'dragon-glow' ) . '</li></ul>'
				. '<p><strong>' . esc_html__( 'Information we collect automatically:', 'dragon-glow' ) . '</strong></p>'
				. '<ul><li><strong>' . esc_html__( 'Device and technical data:', 'dragon-glow' ) . '</strong> ' . esc_html__( 'IP address, device type, browser, operating system, language.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Usage data:', 'dragon-glow' ) . '</strong> ' . esc_html__( 'pages viewed, links clicked, time on page, referring URLs.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Cookies and similar technologies', 'dragon-glow' ) . '</strong>, ' . esc_html__( 'as described in Section 6.', 'dragon-glow' ) . '</li></ul>'
				. '<p><strong>' . esc_html__( 'Information from third parties:', 'dragon-glow' ) . '</strong></p>'
				. '<ul><li>' . esc_html__( 'Payment and fraud-prevention providers, shipping carriers, analytics providers, and social or advertising platforms, where you interact with us through them.', 'dragon-glow' ) . '</li></ul>'
				. $p( __( 'We do not knowingly collect special category data and ask that you do not send it to us.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-4',
			'num'   => '04',
			'title' => __( 'How we use your information', 'dragon-glow' ),
			'body'  => $p( __( 'We use personal information to:', 'dragon-glow' ) )
				. '<ul><li>' . esc_html__( 'Process and deliver your orders, including payment, shipping, returns, and refunds.', 'dragon-glow' ) . '</li>'
				. '<li>' . esc_html__( 'Create and manage your account and keep it secure.', 'dragon-glow' ) . '</li>'
				. '<li>' . esc_html__( 'Respond to your questions and provide customer care.', 'dragon-glow' ) . '</li>'
				. '<li>' . esc_html__( 'Send transactional messages (order confirmations, shipping updates, security notices).', 'dragon-glow' ) . '</li>'
				. '<li>' . esc_html__( 'Send marketing communications where you have opted in, and measure their effectiveness.', 'dragon-glow' ) . '</li>'
				. '<li>' . esc_html__( 'Operate, analyse, and improve our website and products.', 'dragon-glow' ) . '</li>'
				. '<li>' . esc_html__( 'Detect, prevent, and investigate fraud, abuse, and security incidents.', 'dragon-glow' ) . '</li>'
				. '<li>' . esc_html__( 'Comply with legal, tax, accounting, and audit obligations.', 'dragon-glow' ) . '</li>'
				. '<li>' . esc_html__( 'Establish, exercise, or defend legal claims.', 'dragon-glow' ) . '</li></ul>',
		),
		array(
			'id'    => 'section-5',
			'num'   => '05',
			'title' => __( 'Legal bases (EEA/UK)', 'dragon-glow' ),
			'body'  => $p( __( 'Where the GDPR or UK GDPR applies, we rely on the following legal bases:', 'dragon-glow' ) )
				. '<ul><li><strong>' . esc_html__( 'Performance of a contract:', 'dragon-glow' ) . '</strong> ' . esc_html__( 'to fulfil your order and manage your account.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Consent:', 'dragon-glow' ) . '</strong> ' . esc_html__( 'for optional cookies and marketing communications; you may withdraw consent at any time.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Legitimate interests:', 'dragon-glow' ) . '</strong> ' . esc_html__( 'to secure our services, prevent fraud, and improve our products, balanced against your rights.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Legal obligation:', 'dragon-glow' ) . '</strong> ' . esc_html__( 'to meet tax, accounting, and audit requirements.', 'dragon-glow' ) . '</li></ul>'
				. $p( __( 'Where we rely on legitimate interests, you may object as described in Section 11.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-6',
			'num'   => '06',
			'title' => __( 'Cookies and similar technologies', 'dragon-glow' ),
			'body'  => $p( __( 'We use strictly necessary cookies to operate the cart, session, and security features. With your consent, we use analytics and advertising cookies to understand usage and personalise content. You can manage your choices through our cookie banner and your browser settings. For details, see our Cookie Policy.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-7',
			'num'   => '07',
			'title' => __( 'How we share your information', 'dragon-glow' ),
			'body'  => $p( __( 'We share personal information with:', 'dragon-glow' ) )
				. '<ul><li><strong>' . esc_html__( 'Service providers acting on our behalf:', 'dragon-glow' ) . '</strong> ' . esc_html__( 'payment processors, shipping carriers, IT and hosting, analytics, and customer-support tools, each bound by contract to protect your data and use it only as instructed.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Professional advisers and auditors', 'dragon-glow' ) . '</strong>, ' . esc_html__( 'where required.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Authorities and regulators', 'dragon-glow' ) . '</strong>, ' . esc_html__( 'where the law requires or permits.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'A successor entity', 'dragon-glow' ) . '</strong>, ' . esc_html__( 'in connection with a merger, acquisition, or sale of assets, subject to this Policy.', 'dragon-glow' ) . '</li></ul>'
				. $p( __( "We do not sell your personal information for money. Some sharing for advertising may be considered a 'sale' or 'sharing' under California law; see Sections 12 and 13.", 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-8',
			'num'   => '08',
			'title' => __( 'International data transfers', 'dragon-glow' ),
			'body'  => '<p>' . sprintf( wp_kses( __( 'We may transfer personal information to countries outside your own, including the United States. Where we transfer data from the EEA, UK, or Switzerland, we use appropriate safeguards such as the European Commission’s Standard Contractual Clauses and the UK Addendum, along with supplementary measures where needed. You may request a copy of the safeguards we use by contacting <a href="%1$s">%2$s</a>.', 'dragon-glow' ), array( 'a' => array( 'href' => array() ) ) ), 'mailto:privacy@dragonglows.page.gd', 'privacy@dragonglows.page.gd' ) . '</p>',
		),
		array(
			'id'    => 'section-9',
			'num'   => '09',
			'title' => __( 'How long we keep your information', 'dragon-glow' ),
			'body'  => $p( __( 'We keep personal information only as long as necessary for the purposes in this Policy.', 'dragon-glow' ) )
				. '<ul><li><strong>' . esc_html__( 'Account data', 'dragon-glow' ) . '</strong> ' . esc_html__( 'is kept while your account is active.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Transactional records', 'dragon-glow' ) . '</strong> ' . esc_html__( 'are retained for the period required by tax and audit law, generally seven years.', 'dragon-glow' ) . '</li></ul>'
				. $p( __( 'When data is no longer required, we anonymize or delete it. You will hear back on erasure requests within thirty days.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-10',
			'num'   => '10',
			'title' => __( 'How we protect your information', 'dragon-glow' ),
			'body'  => $p( __( 'We use technical and organisational measures appropriate to the risk, including encryption in transit, access controls, and regular review. Passwords are stored hashed, and password reset links expire after fifteen minutes. You can enable Multi-Factor Authentication in your account’s Security Preferences.', 'dragon-glow' ) )
				. $p( __( 'No method of transmission or storage is completely secure; we cannot guarantee absolute security.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-11',
			'num'   => '11',
			'title' => __( 'Your privacy rights (EEA/UK – GDPR)', 'dragon-glow' ),
			'body'  => $p( __( 'If you are in the EEA or UK, you have the right to:', 'dragon-glow' ) )
				. '<ul><li><strong>' . esc_html__( 'Access', 'dragon-glow' ) . '</strong> ' . esc_html__( 'the personal information we hold about you.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Rectify', 'dragon-glow' ) . '</strong> ' . esc_html__( 'inaccurate or incomplete data.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Erase', 'dragon-glow' ) . '</strong> ' . esc_html__( "your data ('right to be forgotten'), subject to legal exceptions.", 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Restrict or object', 'dragon-glow' ) . '</strong> ' . esc_html__( 'to certain processing, including direct marketing.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Data portability', 'dragon-glow' ) . '</strong>, ' . esc_html__( 'to receive your data in a structured, machine-readable format.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Withdraw consent', 'dragon-glow' ) . '</strong> ' . esc_html__( 'at any time, without affecting prior processing.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Lodge a complaint', 'dragon-glow' ) . '</strong> ' . sprintf( wp_kses( __( 'with your supervisory authority. In the UK, this is the Information Commissioner’s Office (<a href="%1$s">%2$s</a>). In the EU, our lead authority is the Irish Data Protection Commission (<a href="%3$s">%4$s</a>), and you may also contact the authority in your country of residence.', 'dragon-glow' ), array( 'a' => array( 'href' => array() ) ) ), 'https://ico.org.uk', 'ico.org.uk', 'https://dataprotection.ie', 'dataprotection.ie' ) . '</li></ul>'
				. '<p>' . sprintf( wp_kses( __( 'To exercise any right, contact <a href="%1$s">%2$s</a>. We respond within one month, extendable by two further months for complex requests, and may verify your identity first.', 'dragon-glow' ), array( 'a' => array( 'href' => array() ) ) ), 'mailto:privacy@dragonglows.page.gd', 'privacy@dragonglows.page.gd' ) . '</p>',
		),
		array(
			'id'    => 'section-12',
			'num'   => '12',
			'title' => __( 'Your privacy rights (California – CCPA/CPRA)', 'dragon-glow' ),
			'body'  => $p( __( 'If you are a California resident, you have the right to:', 'dragon-glow' ) )
				. '<ul><li><strong>' . esc_html__( 'Know', 'dragon-glow' ) . '</strong> ' . esc_html__( 'the categories and specific pieces of personal information we collect, use, disclose, and sell or share.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Delete', 'dragon-glow' ) . '</strong> ' . esc_html__( 'personal information we have collected, subject to exceptions.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Correct', 'dragon-glow' ) . '</strong> ' . esc_html__( 'inaccurate personal information.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Opt out', 'dragon-glow' ) . '</strong> ' . esc_html__( 'of the sale or sharing of personal information.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Limit', 'dragon-glow' ) . '</strong> ' . esc_html__( 'the use and disclosure of sensitive personal information.', 'dragon-glow' ) . '</li>'
				. '<li><strong>' . esc_html__( 'Non-discrimination', 'dragon-glow' ) . '</strong> ' . esc_html__( 'for exercising your rights.', 'dragon-glow' ) . '</li></ul>'
				. $p( __( 'In the preceding twelve months, we have collected the categories of personal information described in Section 3 and disclosed them to the recipients described in Section 7. We do not sell personal information for money. To exercise your rights, submit a request to our privacy team or through the link in Section 13. You may use an authorised agent; we will verify the agent’s authority and your identity.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-13',
			'num'   => '13',
			'title' => __( 'Do Not Sell or Share My Personal Information', 'dragon-glow' ),
			'body'  => '<p>' . sprintf( wp_kses( __( 'To opt out of any sale or sharing of your personal information and to limit the use of sensitive personal information, use the &lsquo;Do Not Sell or Share My Personal Information&rsquo; link in the website footer, adjust your cookie choices, or email <a href="%1$s">%2$s</a>. We also honour Global Privacy Control (GPC) signals where required.', 'dragon-glow' ), array( 'a' => array( 'href' => array() ) ) ), 'mailto:privacy@dragonglows.page.gd', 'privacy@dragonglows.page.gd' ) . '</p>',
		),
		array(
			'id'    => 'section-14',
			'num'   => '14',
			'title' => __( 'Other U.S. state rights', 'dragon-glow' ),
			'body'  => $p( __( 'Residents of other U.S. states with privacy laws (including Virginia, Colorado, Connecticut, and Utah) may have similar rights to access, correct, delete, and opt out of targeted advertising or profiling. Contact our privacy team to exercise these rights, and to appeal a decision where the law provides an appeal.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-15',
			'num'   => '15',
			'title' => __( 'Marketing communications', 'dragon-glow' ),
			'body'  => $p( __( 'Where you opt in, we send marketing by email. You can unsubscribe at any time using the link in any message or by emailing our privacy team. Transactional messages about your orders and account are not marketing and will continue.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-16',
			'num'   => '16',
			'title' => __( 'Automated decision-making', 'dragon-glow' ),
			'body'  => $p( __( 'We do not make decisions producing legal or similarly significant effects about you based solely on automated processing. We use limited automated tools for fraud prevention; a human reviews material decisions.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-17',
			'num'   => '17',
			'title' => __( "Children's privacy", 'dragon-glow' ),
			'body'  => $p( __( 'Our website is not directed to children under 16, and we do not knowingly collect their personal information. If you believe a child has provided us data, contact our privacy team and we will delete it.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-18',
			'num'   => '18',
			'title' => __( 'Third-party links', 'dragon-glow' ),
			'body'  => $p( __( 'Our website may link to third-party sites and services. We are not responsible for their privacy practices. Review their policies before providing personal information.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-19',
			'num'   => '19',
			'title' => __( 'Changes to this Policy', 'dragon-glow' ),
			'body'  => $p( __( "We may update this Policy from time to time. We will revise the 'Last updated' date above and, for material changes, notify you by email or a notice on the website before they take effect.", 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-20',
			'num'   => '20',
			'title' => __( 'How to contact us', 'dragon-glow' ),
			'body'  => '<p><strong>' . sprintf( wp_kses( __( 'Privacy team: <a href="%1$s">%2$s</a>', 'dragon-glow' ), array( 'a' => array( 'href' => array() ) ) ), 'mailto:privacy@dragonglows.page.gd', 'privacy@dragonglows.page.gd' ) . '</strong></p>'
				. '<p><strong>' . sprintf( wp_kses( __( 'Customer care: <a href="%1$s">%2$s</a>', 'dragon-glow' ), array( 'a' => array( 'href' => array() ) ) ), 'mailto:concierge@dragonglows.page.gd', 'concierge@dragonglows.page.gd' ) . '</strong></p>'
				. '<p><strong>' . sprintf( wp_kses( __( 'Data Protection Officer: <a href="%1$s">%2$s</a>', 'dragon-glow' ), array( 'a' => array( 'href' => array() ) ) ), 'mailto:dpo@dragonglows.page.gd', 'dpo@dragonglows.page.gd' ) . '</strong></p>'
				. '<p><strong>' . esc_html__( 'Postal:', 'dragon-glow' ) . '</strong> ' . esc_html__( 'Dragon Glow, Inc., 215 Mercer Street, Suite 400, New York, NY 10012, United States', 'dragon-glow' ) . '</p>'
				. '<address class="dg-privacy-address"><p><strong>' . esc_html__( 'EU representative (Art. 27 GDPR):', 'dragon-glow' ) . '</strong></p><p>Glow Compliance Ltd</p><p>25 Merrion Square, Dublin 2, D02 PX67, Ireland</p><p>' . sprintf( wp_kses( '<a href="%1$s">%2$s</a>', array( 'a' => array( 'href' => array() ) ) ), 'mailto:eu-rep@dragonglows.page.gd', 'eu-rep@dragonglows.page.gd' ) . '</p></address>'
				. '<address class="dg-privacy-address"><p><strong>' . esc_html__( 'UK representative:', 'dragon-glow' ) . '</strong></p><p>Glow Compliance UK Ltd</p><p>40 Berkeley Square, London W1J 5AL, United Kingdom</p><p>' . sprintf( wp_kses( '<a href="%1$s">%2$s</a>', array( 'a' => array( 'href' => array() ) ) ), 'mailto:uk-rep@dragonglows.page.gd', 'uk-rep@dragonglows.page.gd' ) . '</p></address>',
		),
	);

	/**
	 * Lọc danh sách section trước khi render.
	 *
	 * @param array $sections Danh sách section của Privacy Policy.
	 */
	return (array) apply_filters( 'dg_privacy_policy_sections_data', $sections );
}
