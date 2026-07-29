<?php
/**
 * Rule-based educational assistant.
 * Answers only HPV/HSV-related questions using keyword matching against
 * the same content stored in the `content` table, plus a curated set of
 * common-question responses. No external API calls, no cost.
 *
 * IMPORTANT: This is educational information only, not medical advice.
 */

function is_hpv_hsv_related($question) {
    $keywords = [
        'hpv', 'hsv', 'herpes', 'papilloma', 'wart', 'genital', 'std', 'sti',
        'sexual', 'vaccine', 'gardasil', 'pap smear', 'cervical', 'outbreak',
        'antiviral', 'acyclovir', 'valacyclovir', 'cold sore', 'blister',
        'screening', 'symptom', 'transmit', 'transmission', 'infection', 'condom'
    ];
    $q = strtolower($question);
    foreach ($keywords as $k) {
        if (strpos($q, $k) !== false) return true;
    }
    return false;
}

function get_ai_response($question) {
    $q = strtolower(trim($question));

    if (!is_hpv_hsv_related($q)) {
        return "I'm focused specifically on HPV and HSV education, so I can't help with that one. " .
               "Try asking me about symptoms, prevention, vaccination, transmission, or treatment for HPV or HSV.";
    }

    // Full names
    if (preg_match('/full name|stand for|what does hpv mean|what does hsv mean/', $q)) {
        if (strpos($q, 'hsv') !== false) {
            return "HSV stands for Herpes Simplex Virus. There are two types: HSV-1 (commonly oral herpes) and HSV-2 (commonly genital herpes), though either type can appear in either location.";
        }
        return "HPV stands for Human Papillomavirus — a group of more than 200 related viruses, some of which spread through sexual contact.";
    }

    // What is HPV / HSV
    if (preg_match('/what is hpv/', $q)) {
        return "HPV (Human Papillomavirus) is a group of more than 200 related viruses. Some types cause genital warts, others can lead to cell changes linked to cervical, anal, or throat cancer. Most infections clear on their own within two years.";
    }
    if (preg_match('/what is hsv/', $q)) {
        return "HSV (Herpes Simplex Virus) causes cold sores (HSV-1) or genital herpes (HSV-2), though both types can occur in either location. It's very common, manageable with antivirals, and most people with HSV live full, healthy lives.";
    }

    // Symptoms
    if (preg_match('/symptom/', $q)) {
        if (strpos($q, 'hsv') !== false || strpos($q, 'herpes') !== false) {
            return "Common HSV symptoms include blisters or sores around the mouth or genitals, itching or tingling before an outbreak, and flu-like symptoms during a first outbreak. Many people, however, have no noticeable symptoms at all.";
        }
        if (strpos($q, 'hpv') !== false) {
            return "Most HPV infections cause no symptoms at all. Some types cause visible genital warts, while high-risk types can cause cellular changes detectable through Pap smears, which is why regular screening matters.";
        }
        return "Symptoms differ between HPV and HSV — ask me specifically about 'HPV symptoms' or 'HSV symptoms' for details.";
    }

    // Prevention
    if (preg_match('/prevent|avoid|protect/', $q)) {
        if (strpos($q, 'hsv') !== false || strpos($q, 'herpes') !== false) {
            return "HSV prevention includes consistent condom use (which reduces but doesn't eliminate risk), avoiding sexual contact during outbreaks, open communication with partners, and daily antiviral therapy which can lower transmission risk by about 50%.";
        }
        return "HPV prevention centers on the Gardasil 9 vaccine (recommended ages 9–45), consistent condom use, and regular Pap smears / HPV testing for early detection.";
    }

    // Vaccine
    if (preg_match('/vaccine|gardasil|shot|immuniz/', $q)) {
        return "The HPV vaccine (Gardasil 9) protects against the HPV types responsible for most cervical, anal, and throat cancers. It's recommended for ages 9–45 and given as a 2 or 3-dose series depending on age at the first dose. There is currently no vaccine for HSV.";
    }

    // Treatment / medication
    if (preg_match('/treat|medicat|cure|acyclovir|valacyclovir|famciclovir/', $q)) {
        if (strpos($q, 'hsv') !== false || strpos($q, 'herpes') !== false) {
            return "HSV has no cure, but antiviral medications (Acyclovir, Valacyclovir, Famciclovir) reduce outbreak frequency and severity. Daily suppressive therapy can also lower the chance of passing it to a partner.";
        }
        return "There's no cure for HPV itself, but the body clears most infections naturally. Genital warts can be treated with topical creams, cryotherapy, or removal, and precancerous cell changes can be treated with procedures like LEEP.";
    }

    // Transmission
    if (preg_match('/transmit|spread|contagious|catch/', $q)) {
        if (strpos($q, 'hsv') !== false || strpos($q, 'herpes') !== false) {
            return "HSV spreads through skin-to-skin contact — oral-to-oral (HSV-1, e.g. kissing) or during vaginal, anal, or oral sex (either type). It can spread even without visible sores.";
        }
        return "HPV spreads through skin-to-skin contact during vaginal, anal, or oral sex, and rarely from mother to baby during childbirth. It can spread even when no warts are visible.";
    }

    // Testing / screening
    if (preg_match('/test|screen|diagnos/', $q)) {
        return "Testing is the only reliable way to know your status, since many HPV and HSV infections cause no symptoms. HPV is typically detected via Pap smear / HPV co-testing; HSV via a blood test or swab of an active sore. Use the Booking page to schedule a confidential screening.";
    }

    // Default fallback
    return "That's a great question about sexual health. I can share general education on HPV or HSV symptoms, prevention, vaccination, treatment, and transmission — try rephrasing with one of those topics, or check the HPV/HSV pages for full detail.";
}
