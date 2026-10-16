<section class="page-hero page-hero--minimal">
    <div class="container page-hero__inner">
        <p class="page-hero__eyebrow">Contact</p>
        <h1 class="page-hero__headline">Let's talk.</h1>
        <p class="page-hero__lede">
            Reach MWAMBE Agency directly — or send us a message and we'll get back to you.
        </p>
    </div>
</section>

<section class="section">
    <div class="container container--narrow">

        <?php if (!empty($success)): ?>
            <div class="alert alert--success" role="status"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors['_form'])): ?>
            <div class="alert alert--error" role="alert"><?= e($errors['_form']) ?></div>
        <?php endif; ?>

        <div class="contact-grid">
            <div class="contact-grid__info">
                <h2 class="section__title">MWAMBE Agency</h2>
                <address>
                    <p>Lilongwe, Malawi</p>
                    <p>Email: <a href="mailto:mwambeagency@gmail.com">mwambeagency@gmail.com</a></p>
                    <p>Telephone: <a href="tel:+265995111156">+265 995 111 156</a><br>
                       <a href="tel:+265882315575">+265 882 315 575</a></p>
                </address>
            </div>

            <form method="post" action="/contact" class="form" novalidate>
                <?= $csrf->field() ?>

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