<section class="page-hero page-hero--minimal">
    <div class="page-hero__bg" aria-hidden="true">
        <img
            src="<?= e(asset('uploads/2026/10/2369d12bad240e561d82423f.jpg')) ?>"
            alt=""
            width="1000" height="667"
            fetchpriority="high">
    </div>
    <div class="container page-hero__inner">
        <p class="page-hero__eyebrow">Contact</p>
        <h1 class="page-hero__headline">Let's talk.</h1>
        <p class="page-hero__lede">
            Reach MWAMBE Agency directly — or send us a message and we'll get back to you.
        </p>
    </div>
</section>

<section class="section contact-page">
    <div class="container">

        <?php if (!empty($success)): ?>
            <div class="alert alert--success" role="status"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors['_form'])): ?>
            <div class="alert alert--error" role="alert"><?= e($errors['_form']) ?></div>
        <?php endif; ?>

        <div class="contact-grid">
            <aside class="contact-grid__info" aria-labelledby="contact-info-title">
                <p class="eyebrow">Reach out directly</p>
                <h2 class="section__title" id="contact-info-title">We’re here to connect.</h2>
                <p class="contact-grid__intro">Choose the channel that works best for you, or send a message and our team will be in touch.</p>
                <address>
                    <p class="contact-detail">
                        <span class="contact-detail__icon"><svg class="contact-icon" aria-hidden="true"><use href="#icon-pin"></use></svg></span>
                        <span><strong>Visit</strong><br>Lilongwe, Malawi</span>
                    </p>
                    <p class="contact-detail">
                        <span class="contact-detail__icon"><svg class="contact-icon" aria-hidden="true"><use href="#icon-envelope"></use></svg></span>
                        <span><strong>Email</strong><br><a href="mailto:mwambeagency@gmail.com">mwambeagency@gmail.com</a></span>
                    </p>
                    <p class="contact-detail">
                        <span class="contact-detail__icon"><svg class="contact-icon" aria-hidden="true"><use href="#icon-phone"></use></svg></span>
                        <span><strong>Call</strong><br><a href="tel:+265995111156">+265 995 111 156</a><br>
                        <a href="tel:+265882315575">+265 882 315 575</a></span>
                    </p>
                </address>
                <a class="contact-grid__link" href="/partner-with-us">Interested in partnering? <span aria-hidden="true">→</span></a>
            </aside>

            <form method="post" action="/contact" class="form contact-form" novalidate aria-labelledby="contact-form-title">
                <?= $csrf->field() ?>
                <header class="enquiry-form__header">
                    <p class="eyebrow">Send a message</p>
                    <h2 id="contact-form-title">How can we help?</h2>
                    <p>Fields marked <span class="form__required">*</span> are required.</p>
                </header>

                <!-- honeypot -->
                <div class="form__honeypot" aria-hidden="true" style="position:absolute;left:-9999px">
                    <label for="website">Website (leave empty)</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="form__row">
                    <label for="name" class="form__label">Your name <span class="form__required">*</span></label>
                    <input type="text" id="name" name="name" class="form__input" required
                           value="<?= e($old['name'] ?? '') ?>"
                           aria-invalid="<?= isset($errors['name']) ? 'true' : 'false' ?>">
                    <?php if (isset($errors['name'])): ?><p class="form__error"><?= e($errors['name']) ?></p><?php endif; ?>
                </div>

                <div class="form__row">
                    <label for="email" class="form__label">Email <span class="form__required">*</span></label>
                    <input type="email" id="email" name="email" class="form__input" required
                           value="<?= e($old['email'] ?? '') ?>"
                           aria-invalid="<?= isset($errors['email']) ? 'true' : 'false' ?>">
                    <?php if (isset($errors['email'])): ?><p class="form__error"><?= e($errors['email']) ?></p><?php endif; ?>
                </div>

                <div class="form__row">
                    <label for="phone" class="form__label">Telephone</label>
                    <input type="tel" id="phone" name="phone" class="form__input"
                           value="<?= e($old['phone'] ?? '') ?>">
                </div>

                <div class="form__row">
                    <label for="subject" class="form__label">Subject</label>
                    <input type="text" id="subject" name="subject" class="form__input"
                           value="<?= e($old['subject'] ?? '') ?>">
                </div>

                <div class="form__row">
                    <label for="message" class="form__label">Message <span class="form__required">*</span></label>
                    <textarea id="message" name="message" class="form__textarea" rows="6" required
                              aria-invalid="<?= isset($errors['message']) ? 'true' : 'false' ?>"><?= e($old['message'] ?? '') ?></textarea>
                    <?php if (isset($errors['message'])): ?><p class="form__error"><?= e($errors['message']) ?></p><?php endif; ?>
                </div>

                <div class="form__row form__row--check">
                    <input type="checkbox" id="privacy_ack" name="privacy_ack" value="1"
                           <?= !empty($old['privacy_ack']) ? 'checked' : '' ?>>
                    <label for="privacy_ack">I have read the <a href="/privacy">privacy notice</a> and agree to my details being used to respond to this enquiry. <span class="form__required">*</span></label>
                    <?php if (isset($errors['privacy_ack'])): ?><p class="form__error"><?= e($errors['privacy_ack']) ?></p><?php endif; ?>
                </div>

                <button type="submit" class="btn btn--primary">Send message</button>
            </form>
        </div>
    </div>
</section>

<section class="section section--alt contact-map" aria-labelledby="contact-map-title">
    <div class="container">
        <div class="contact-map__header">
            <div>
                <p class="eyebrow">Find us</p>
                <h2 class="section__title" id="contact-map-title">Based in Lilongwe, Malawi.</h2>
                <p class="section__lede">Get directions to MWAMBE Agency in Lilongwe.</p>
            </div>
            <a class="btn btn--primary" href="https://www.google.com/maps/search/?api=1&amp;query=Lilongwe%2C%20Malawi" target="_blank" rel="noopener noreferrer">
                Open map <span aria-hidden="true">↗</span>
            </a>
        </div>
        <div class="contact-map__frame">
            <iframe
                src="https://maps.google.com/maps?q=Lilongwe%2C%20Malawi&amp;output=embed"
                title="Map showing Lilongwe, Malawi"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen></iframe>
        </div>
    </div>
</section>