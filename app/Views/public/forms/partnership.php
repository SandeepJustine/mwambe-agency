<section class="page-hero page-hero--minimal">
    <div class="container page-hero__inner">
        <p class="page-hero__eyebrow">Partner With Us</p>
        <h1 class="page-hero__headline">Start a conversation.</h1>
        <p class="page-hero__lede">
            Tell us about your organisation and the collaboration you have in mind.
            We read every enquiry.
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

        <form method="post" action="/partner-with-us" class="form" novalidate>
            <?= $csrf->field() ?>

            <div class="form__honeypot" aria-hidden="true" style="position:absolute;left:-9999px">
                <label for="website">Website (leave empty)</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="form__row">
                <label for="organisation_name" class="form__label">Organisation or individual name <span class="form__required">*</span></label>
                <input type="text" id="organisation_name" name="organisation_name" class="form__input" required
                       value="<?= e($old['organisation_name'] ?? '') ?>">
                <?php if (isset($errors['organisation_name'])): ?><p class="form__error"><?= e($errors['organisation_name']) ?></p><?php endif; ?>
            </div>

            <div class="form__grid">
                <div class="form__row">
                    <label for="contact_person" class="form__label">Contact person <span class="form__required">*</span></label>
                    <input type="text" id="contact_person" name="contact_person" class="form__input" required
                           value="<?= e($old['contact_person'] ?? '') ?>">
                    <?php if (isset($errors['contact_person'])): ?><p class="form__error"><?= e($errors['contact_person']) ?></p><?php endif; ?>
                </div>
                <div class="form__row">
                    <label for="email" class="form__label">Email <span class="form__required">*</span></label>
                    <input type="email" id="email" name="email" class="form__input" required
                           value="<?= e($old['email'] ?? '') ?>">
                    <?php if (isset($errors['email'])): ?><p class="form__error"><?= e($errors['email']) ?></p><?php endif; ?>
                </div>
                <div class="form__row">
                    <label for="phone" class="form__label">Telephone</label>
                    <input type="tel" id="phone" name="phone" class="form__input"
                           value="<?= e($old['phone'] ?? '') ?>">
                </div>
                <div class="form__row">
                    <label for="organisation_type" class="form__label">Organisation type</label>
                    <select id="organisation_type" name="organisation_type" class="form__select">
                        <?php
                        $types = ['', 'Development organisation', 'NGO / civil society', 'Private company', 'Financial institution', 'SME / business association', 'Government institution', 'University / training institution', 'Professional network', 'Cooperative / community group', 'Technical expert / mentor', 'Youth-focused organisation', 'Individual'];
                        foreach ($types as $t):
                            $sel = ($old['organisation_type'] ?? '') === $t ? ' selected' : '';
                        ?>
                            <option value="<?= e($t) ?>"<?= $sel ?>><?= e($t ?: '— Select —') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form__row">
                <label for="partnership_interest" class="form__label">Partnership interest</label>
                <input type="text" id="partnership_interest" name="partnership_interest" class="form__input"
                       placeholder="e.g. Programme co-design, funding, mentorship"
                       value="<?= e($old['partnership_interest'] ?? '') ?>">
            </div>

            <div class="form__row">
                <label for="proposed_collaboration" class="form__label">Proposed collaboration <span class="form__required">*</span></label>
                <textarea id="proposed_collaboration" name="proposed_collaboration" class="form__textarea" rows="6" required><?= e($old['proposed_collaboration'] ?? '') ?></textarea>
                <?php if (isset($errors['proposed_collaboration'])): ?><p class="form__error"><?= e($errors['proposed_collaboration']) ?></p><?php endif; ?>
            </div>

            <div class="form__row">
                <label for="additional_message" class="form__label">Additional message</label>
                <textarea id="additional_message" name="additional_message" class="form__textarea" rows="4"><?= e($old['additional_message'] ?? '') ?></textarea>
            </div>

            <div class="form__row form__row--check">
                <input type="checkbox" id="privacy_ack" name="privacy_ack" value="1"
                       <?= !empty($old['privacy_ack']) ? 'checked' : '' ?>>
                <label for="privacy_ack">I have read the <a href="/privacy">privacy notice</a> and agree to my details being used to respond to this enquiry. <span class="form__required">*</span></label>
                <?php if (isset($errors['privacy_ack'])): ?><p class="form__error"><?= e($errors['privacy_ack']) ?></p><?php endif; ?>
            </div>

            <button type="submit" class="btn btn--primary">Send partnership enquiry</button>
        </form>
    </div>
</section>