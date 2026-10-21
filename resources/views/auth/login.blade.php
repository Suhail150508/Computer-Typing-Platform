@extends('layouts.base')

@section('title', 'লগইন')
@section('meta_description', 'TypingMoja-তে লগইন করুন এবং বাংলা ও ইংরেজি টাইপিং স্পিড বাড়ানোর অনুশীলন চালিয়ে যান।')
@section('meta_robots', 'index, follow')

@push('head')
<style>
/* =====================================================
   TYPINGBD LOGIN — PREMIUM TYPING PLATFORM
   All styles are scoped to .tb-login-page
   ===================================================== */

.tb-login-page,
.tb-login-page * {
    box-sizing: border-box;
}

.tb-login-page {
    min-height: 100vh;
    overflow: hidden;
    color: #0f172a;
    background: #f8faff;
    font-family: inherit;
}

.tb-login-page .tb-layout {
    display: grid;
    grid-template-columns: 34% 66%;
    min-height: 100vh;
}

/* ================ LEFT PANEL ================ */

.tb-login-page .tb-left {
    position: relative;
    display: flex;
    align-items: flex-start; /* center এর বদলে flex-start দিন যাতে উপর থেকে শুরু হয় */
    justify-content: center;
    min-width: 0;
    min-height: 100vh;
    padding: 40px clamp(22px, 3vw, 42px) 20px; /* টপ প্যাডিং বাড়িয়ে এবং বোটম কমিয়ে উপরে উঠানো হয়েছে */
    overflow-y: auto; /* স্ক্রিন ছোট হলে যাতে স্ক্রল করা যায় */
    background:
        radial-gradient(circle at 0 0,
            rgba(99,102,241,.11), transparent 30%),
        radial-gradient(circle at 100% 100%,
            rgba(37,99,235,.08), transparent 30%),
        #fff;
}

.tb-login-page .tb-left::before,
.tb-login-page .tb-left::after {
    content: "";
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}

.tb-login-page .tb-left::before {
    width: 210px;
    height: 210px;
    top: -155px;
    left: -120px;
    border: 40px solid rgba(99,102,241,.08);
}

.tb-login-page .tb-left::after {
    width: 190px;
    height: 190px;
    right: -140px;
    bottom: -100px;
    border: 38px solid rgba(37,99,235,.07);
}

.tb-login-page .tb-card {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 385px;
    animation: tbEnter .7s ease both;
}

@keyframes tbEnter {
    from {
        opacity: 0;
        transform: translateY(18px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Brand */

.tb-login-page .tb-brand {
    display: inline-flex;
    align-items: center;
    gap: 11px;
    margin-bottom: 23px;
    text-decoration: none;
}

.tb-login-page .tb-brand-icon {
    display: grid;
    place-items: center;
    width: 45px;
    height: 45px;
    border-radius: 14px;
    color: white;
    background: linear-gradient(135deg, #6366f1, #2563eb);
    box-shadow: 0 9px 24px rgba(79,70,229,.28);
    font: italic 900 25px Arial, sans-serif;
    animation: tbBrandFloat 4s ease-in-out infinite;
}

@keyframes tbBrandFloat {
    0%, 100% { transform: translateY(0) rotate(-3deg); }
    50% { transform: translateY(-4px) rotate(3deg); }
}

.tb-login-page .tb-brand-name {
    color: #14244a;
    font: 900 25px Arial, sans-serif;
    letter-spacing: -1px;
}

.tb-login-page .tb-brand-name span {
    color: #2563eb;
}

/* Heading */

.tb-login-page .tb-kicker {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    margin-bottom: 12px;
    padding: 6px 10px;
    border: 1px solid #dbeafe;
    border-radius: 30px;
    color: #1d4ed8;
    background: #eff6ff;
    font-size: 10px;
    font-weight: 800;
}

.tb-login-page .tb-kicker-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 0 4px rgba(34,197,94,.12);
}

.tb-login-page .tb-heading {
    margin: 0 0 9px;
    color: #14244a;
    font-size: clamp(27px, 2.3vw, 33px);
    line-height: 1.3;
    font-weight: 900;
    letter-spacing: -.6px;
}

.tb-login-page .tb-subtitle {
    margin: 0 0 23px;
    color: #718096;
    font-size: 12px;
    line-height: 1.9;
}

/* Session messages */

.tb-login-page .tb-alert {
    margin-bottom: 13px;
    padding: 11px 13px;
    border: 1px solid #fecdd3;
    border-radius: 11px;
    color: #be123c;
    background: #fff1f2;
    font-size: 12px;
    line-height: 1.7;
}

.tb-login-page .tb-alert-success {
    border-color: #bbf7d0;
    color: #166534;
    background: #f0fdf4;
}

/* Fields */

.tb-login-page .tb-field {
    position: relative;
    margin-bottom: 13px;
}

.tb-login-page .tb-field input {
    display: block;
    width: 100%;
    height: 55px;
    padding: 17px 44px 7px 44px;
    border: 1.5px solid #dce5f3;
    border-radius: 14px;
    outline: none;
    color: #14244a;
    background: rgba(255,255,255,.95);
    font: inherit;
    font-size: 12px;
    transition: border-color .25s, box-shadow .25s;
}

.tb-login-page .tb-field input:hover {
    border-color: #b8c8e7;
}

.tb-login-page .tb-field input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99,102,241,.09);
}

.tb-login-page .tb-field > label {
    position: absolute;
    top: 18px;
    left: 44px;
    z-index: 1;
    color: #94a3b8;
    font-size: 12px;
    pointer-events: none;
    transition: .2s ease;
}

.tb-login-page .tb-field input:focus + label,
.tb-login-page .tb-field input:not(:placeholder-shown) + label {
    top: 6px;
    color: #6366f1;
    font-size: 9px;
    font-weight: 800;
}

.tb-login-page .tb-field-icon {
    position: absolute;
    top: 50%;
    left: 14px;
    width: 19px;
    height: 19px;
    color: #8092b2;
    transform: translateY(-50%);
    pointer-events: none;
    transition: color .2s;
}

.tb-login-page .tb-field:focus-within .tb-field-icon {
    color: #6366f1;
}

.tb-login-page .tb-eye {
    position: absolute;
    top: 50%;
    right: 6px;
    display: grid;
    place-items: center;
    width: 38px;
    height: 38px;
    border: 0;
    border-radius: 10px;
    color: #8292ad;
    background: transparent;
    cursor: pointer;
    transform: translateY(-50%);
}

.tb-login-page .tb-eye:hover {
    color: #4f46e5;
    background: #f1f5ff;
}

.tb-login-page .tb-error {
    margin: -5px 0 9px;
    color: #e11d48;
    font-size: 11px;
    line-height: 1.6;
}

/* Remember + forgot */

.tb-login-page .tb-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin: 3px 0 17px;
    font-size: 11px;
}

.tb-login-page .tb-remember {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #64748b;
    cursor: pointer;
}

.tb-login-page .tb-remember input {
    width: 15px;
    height: 15px;
    margin: 0;
    accent-color: #4f46e5;
}

.tb-login-page .tb-options a,
.tb-login-page .tb-register a {
    color: #4338ca;
    font-weight: 800;
    text-decoration: none;
}

.tb-login-page .tb-options a:hover,
.tb-login-page .tb-register a:hover {
    text-decoration: underline;
}

/* Submit */

.tb-login-page .tb-submit {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    width: 100%;
    min-height: 53px;
    overflow: hidden;
    border: 0;
    border-radius: 14px;
    color: #fff;
    background: linear-gradient(100deg, #5145ed, #2164f5);
    box-shadow: 0 12px 26px rgba(79,70,229,.24);
    font: inherit;
    font-size: 13px;
    font-weight: 900;
    cursor: pointer;
    transition: transform .25s, box-shadow .25s;
}

.tb-login-page .tb-submit::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(
        110deg,
        transparent 25%,
        rgba(255,255,255,.25) 50%,
        transparent 75%
    );
    transform: translateX(-120%);
    transition: transform .7s;
}

.tb-login-page .tb-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 32px rgba(79,70,229,.34);
}

.tb-login-page .tb-submit:hover::before {
    transform: translateX(120%);
}

.tb-login-page .tb-submit > * {
    position: relative;
    z-index: 1;
}

.tb-login-page .tb-submit:disabled {
    cursor: wait;
    opacity: .8;
}

.tb-login-page .tb-spinner {
    display: none;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255,255,255,.4);
    border-top-color: #fff;
    border-radius: 50%;
    animation: tbSpin .7s linear infinite;
}

@keyframes tbSpin {
    to { transform: rotate(360deg); }
}

/* Divider + Google */

.tb-login-page .tb-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 18px 0;
}

.tb-login-page .tb-divider span {
    height: 1px;
    flex: 1;
    background: #e6ebf4;
}

.tb-login-page .tb-divider small {
    color: #94a3b8;
    font-size: 10px;
}

.tb-login-page .tb-google {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    width: 100%;
    min-height: 48px;
    border: 1.5px solid #dce5f3;
    border-radius: 13px;
    color: #1e293b;
    background: #fff;
    font-size: 12px;
    font-weight: 800;
    text-decoration: none;
    transition: .25s ease;
}

.tb-login-page .tb-google:hover {
    transform: translateY(-2px);
    border-color: #a5b4fc;
    box-shadow: 0 9px 22px rgba(15,23,42,.06);
}

.tb-login-page .tb-google-note {
    margin: 8px 0 0;
    color: #94a3b8;
    text-align: center;
    font-size: 9px;
    line-height: 1.7;
}

/* Register link */

.tb-login-page .tb-register {
    margin: 19px 0 0;
    color: #64748b;
    text-align: center;
    font-size: 12px;
}

.tb-login-page .tb-footer {
    position: absolute;
    right: 15px;
    bottom: 13px;
    left: 15px;
    color: #9aa8bf;
    text-align: center;
    font-size: 9px;
}

/* ================ RIGHT PANEL ================ */

.tb-login-page .tb-right {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 0;
    min-height: 100vh;
    overflow: hidden;
    color: #fff;
    background:
        radial-gradient(ellipse at 78% 15%,
            rgba(79,70,229,.45), transparent 32%),
        radial-gradient(ellipse at 10% 90%,
            rgba(6,182,212,.18), transparent 30%),
        linear-gradient(145deg,#050f30,#0b1b50 53%,#101d62);
}

.tb-login-page .tb-right::before {
    content: "";
    position: absolute;
    inset: 0;
    opacity: .18;
    background-image:
        linear-gradient(rgba(147,197,253,.10) 1px, transparent 1px),
        linear-gradient(90deg,rgba(147,197,253,.10) 1px, transparent 1px);
    background-size: 42px 42px;
    mask-image: linear-gradient(to bottom, black, transparent 90%);
}

.tb-login-page .tb-orb {
    position: absolute;
    border: 1px solid rgba(96,165,250,.18);
    border-radius: 50%;
    pointer-events: none;
    animation: tbOrbFloat 9s ease-in-out infinite;
}

.tb-login-page .tb-orb-one {
    top: -190px;
    right: -80px;
    width: 420px;
    height: 420px;
    box-shadow: 0 0 80px rgba(79,70,229,.15);
}

.tb-login-page .tb-orb-two {
    bottom: -260px;
    left: -100px;
    width: 520px;
    height: 520px;
    border-width: 55px;
    border-color: rgba(59,130,246,.08);
    animation-delay: -4s;
}

@keyframes tbOrbFloat {
    0%,100% { transform: translateY(0) rotate(0); }
    50% { transform: translateY(-14px) rotate(4deg); }
}

.tb-login-page #tbParticles {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.tb-login-page .tb-showcase {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 1040px;
    padding: 32px clamp(25px, 4vw, 62px);
}

/* Hero */

.tb-login-page .tb-hero {
    max-width: 720px;
    margin: 0 auto 23px;
    text-align: center;
}

.tb-login-page .tb-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 13px;
    padding: 7px 12px;
    border: 1px solid rgba(147,197,253,.22);
    border-radius: 30px;
    color: #bfdbfe;
    background: rgba(30,64,175,.2);
    font-size: 9px;
    font-weight: 900;
    letter-spacing: .7px;
    backdrop-filter: blur(10px);
}

.tb-login-page .tb-live-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #34d399;
    box-shadow: 0 0 12px #34d399;
    animation: tbPulse 1.8s infinite;
}

@keyframes tbPulse {
    50% { box-shadow: 0 0 0 7px rgba(52,211,153,.06); }
}

.tb-login-page .tb-hero h2 {
    margin: 0;
    color: #fff;
    font-size: clamp(29px, 3.1vw, 45px);
    line-height: 1.25;
    font-weight: 900;
    letter-spacing: -1px;
}

.hero-title .gradient {
    background: linear-gradient(90deg,#fff,#67e8f9,#a5b4fc);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.tb-login-page .tb-gradient-text {
    background: linear-gradient(90deg,#67e8f9,#a5b4fc,#c4b5fd);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.tb-login-page .tb-hero p {
    max-width: 600px;
    margin: 11px auto 0;
    color: #bac8e4;
    font-size: 12px;
    line-height: 1.9;
}

/* Typing app mockup */

.tb-login-page .tb-dashboard {
    position: relative;
    max-width: 790px;
    margin: 0 auto 18px;
    padding: 14px;
    border: 1px solid rgba(125,211,252,.25);
    border-radius: 22px;
    background: rgba(4,12,39,.66);
    box-shadow:
        0 25px 70px rgba(0,0,0,.28),
        inset 0 1px rgba(255,255,255,.07);
    backdrop-filter: blur(15px);
    animation: tbDashboardFloat 6s ease-in-out infinite;
}

@keyframes tbDashboardFloat {
    0%,100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

.tb-login-page .tb-dash-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
}

.tb-login-page .tb-dash-brand {
    display: flex;
    align-items: center;
    gap: 9px;
}

.tb-login-page .tb-dash-logo {
    display: grid;
    place-items: center;
    width: 35px;
    height: 35px;
    border-radius: 11px;
    color: #fff;
    background: linear-gradient(135deg,#4f46e5,#06b6d4);
    box-shadow: 0 6px 20px rgba(59,130,246,.3);
    font-size: 18px;
}

.tb-login-page .tb-dash-brand strong {
    display: block;
    color: #fff;
    font-size: 11px;
    font-weight: 900;
}

.tb-login-page .tb-dash-brand small {
    display: block;
    margin-top: 3px;
    color: #8ca0c4;
    font-size: 8px;
}

.tb-login-page .tb-live {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 9px;
    border: 1px solid rgba(74,222,128,.22);
    border-radius: 30px;
    color: #bbf7d0;
    background: rgba(34,197,94,.07);
    font-size: 8px;
    font-weight: 800;
}

/* Language switch */

.tb-login-page .tb-language {
    display: flex;
    align-items: center;
    gap: 3px;
    padding: 3px;
    border: 1px solid rgba(148,163,184,.16);
    border-radius: 30px;
    background: rgba(15,23,42,.8);
}

.tb-login-page .tb-language button {
    padding: 6px 10px;
    border: 0;
    border-radius: 25px;
    color: #91a4c6;
    background: transparent;
    font: inherit;
    font-size: 9px;
    font-weight: 800;
    cursor: pointer;
    transition: .2s;
}

.tb-login-page .tb-language button.active {
    color: #fff;
    background: linear-gradient(135deg,#4f46e5,#2563eb);
}

/* Stats */

.tb-login-page .tb-stats {
    display: grid;
    grid-template-columns: repeat(4,minmax(0,1fr));
    gap: 8px;
    margin-bottom: 10px;
}

.tb-login-page .tb-stat {
    padding: 10px;
    border: 1px solid rgba(148,163,184,.13);
    border-radius: 12px;
    background: rgba(15,23,42,.58);
}

.tb-login-page .tb-stat small {
    display: block;
    color: #8ca0c4;
    font-size: 8px;
    font-weight: 800;
}

.tb-login-page .tb-stat strong {
    display: block;
    margin-top: 5px;
    color: #fff;
    font: 900 19px Arial, sans-serif;
}

.tb-login-page .tb-stat strong span {
    color: #67e8f9;
    font-size: 10px;
}

.tb-login-page .tb-stat:first-child strong {
    color: #67e8f9;
}

/* Passage */

.tb-login-page .tb-passage {
    position: relative;
    padding: 13px 14px;
    border: 1px solid rgba(96,165,250,.18);
    border-radius: 13px;
    background: rgba(2,6,23,.5);
}

.tb-login-page .tb-passage-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 8px;
    color: #8ca0c4;
    font-size: 8px;
    font-weight: 800;
}

.tb-login-page .tb-passage-head b {
    color: #67e8f9;
}

.tb-login-page .tb-passage-text {
    min-height: 57px;
    color: #8ea1c3;
    font-size: clamp(11px,1vw,13px);
    line-height: 1.9;
}

.tb-login-page .tb-passage-text .typed {
    color: #e2e8f0;
}

.tb-login-page .tb-passage-text .current {
    padding: 2px 4px;
    border-radius: 4px;
    color: #fff;
    background: rgba(59,130,246,.3);
    box-shadow: 0 0 0 1px rgba(96,165,250,.25);
}

.tb-login-page .tb-caret {
    display: inline-block;
    width: 2px;
    height: 14px;
    margin-left: 3px;
    vertical-align: -2px;
    background: #67e8f9;
    animation: tbCaret .8s step-end infinite;
}

@keyframes tbCaret {
    50% { opacity: 0; }
}

.tb-login-page .tb-progress {
    height: 5px;
    margin-top: 12px;
    overflow: hidden;
    border-radius: 20px;
    background: rgba(148,163,184,.15);
}

.tb-login-page .tb-progress span {
    display: block;
    width: 0;
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg,#4f46e5,#06b6d4);
    box-shadow: 0 0 13px rgba(34,211,238,.45);
    transition: width .5s ease;
}

/* Animated keyboard */

.tb-login-page .tb-keyboard {
    margin-top: 10px;
    padding: 10px 7px;
    border: 1px solid rgba(148,163,184,.12);
    border-radius: 13px;
    background: rgba(2,6,23,.38);
}

.tb-login-page .tb-key-row {
    display: flex;
    justify-content: center;
    gap: 4px;
    margin: 4px 0;
}

.tb-login-page .tb-key {
    display: grid;
    place-items: center;
    min-width: 0;
    width: 7%;
    height: 25px;
    border: 1px solid rgba(148,163,184,.14);
    border-radius: 5px;
    color: #91a4c6;
    background: rgba(30,41,59,.8);
    box-shadow: inset 0 -2px rgba(2,6,23,.4);
    font: 800 8px Arial, sans-serif;
    transition: .15s ease;
}

.tb-login-page .tb-key.active {
    color: #fff;
    border-color: rgba(96,165,250,.7);
    background: linear-gradient(135deg,#4f46e5,#2563eb);
    box-shadow: 0 0 13px rgba(59,130,246,.4);
    transform: translateY(-2px);
}

.tb-login-page .tb-key.space {
    width: 40%;
    max-width: 230px;
}

.tb-login-page .tb-dash-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-top: 10px;
    color: #91a4c6;
    font-size: 9px;
}

.tb-login-page .tb-dash-bottom strong {
    color: #a5b4fc;
}

/* Learning levels */

.tb-login-page .tb-path-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 10px;
    color: #e2e8f0;
    font-size: 10px;
    font-weight: 900;
}

.tb-login-page .tb-path-heading span:last-child {
    color: #67e8f9;
    font-size: 9px;
}

.tb-login-page .tb-levels {
    display: grid;
    grid-template-columns: repeat(3,minmax(0,1fr));
    gap: 9px;
}

.tb-login-page .tb-level {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    padding: 12px 10px;
    overflow: hidden;
    border: 1px solid rgba(147,197,253,.17);
    border-radius: 13px;
    background: rgba(15,23,42,.35);
    transition: transform .25s, border-color .25s,
                background .25s;
}

.tb-login-page .tb-level::after {
    content: "";
    position: absolute;
    top: -25px;
    right: -25px;
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background: rgba(59,130,246,.12);
}

.tb-login-page .tb-level:hover {
    transform: translateY(-4px);
    border-color: rgba(125,211,252,.55);
    background: rgba(30,64,175,.23);
}

.tb-login-page .tb-level-icon {
    position: relative;
    z-index: 1;
    display: grid;
    place-items: center;
    flex: 0 0 36px;
    width: 36px;
    height: 36px;
    border-radius: 11px;
    color: #fff;
    background: linear-gradient(135deg,#10b981,#06b6d4);
    font-size: 17px;
}

.tb-login-page .tb-level:nth-child(2) .tb-level-icon {
    background: linear-gradient(135deg,#0ea5e9,#6366f1);
}

.tb-login-page .tb-level:nth-child(3) .tb-level-icon {
    background: linear-gradient(135deg,#f59e0b,#ec4899);
}

.tb-login-page .tb-level strong {
    display: block;
    color: #fff;
    font-size: 11px;
    font-weight: 900;
}

.tb-login-page .tb-level small {
    display: block;
    margin-top: 4px;
    color: #9aacca;
    font-size: 8px;
    line-height: 1.5;
}

/* Feature strip */

.tb-login-page .tb-features {
    display: grid;
    grid-template-columns: repeat(4,minmax(0,1fr));
    gap: 7px;
    margin-top: 11px;
}

.tb-login-page .tb-feature {
    padding: 10px 5px;
    border: 1px solid rgba(147,197,253,.12);
    border-radius: 10px;
    background: rgba(15,23,42,.25);
    text-align: center;
}

.tb-login-page .tb-feature strong {
    display: block;
    color: #dbeafe;
    font-size: 9px;
    font-weight: 900;
}

.tb-login-page .tb-feature small {
    display: block;
    margin-top: 4px;
    color: #91a4c6;
    font-size: 8px;
}

/* ================ RESPONSIVE ================ */

@media (min-width: 1500px) {
    .tb-login-page .tb-showcase {
        max-width: 1100px;
    }
}

@media (max-width: 1100px) {
    .tb-login-page .tb-layout {
        grid-template-columns: 40% 60%;
    }

    .tb-login-page .tb-left {
        padding-right: 25px;
        padding-left: 25px;
    }

    .tb-login-page .tb-showcase {
        padding-right: 25px;
        padding-left: 25px;
    }

    .tb-login-page .tb-level {
        gap: 7px;
        padding: 9px 7px;
    }

    .tb-login-page .tb-level-icon {
        flex-basis: 30px;
        width: 30px;
        height: 30px;
    }
}

@media (max-width: 900px) {
    .tb-login-page .tb-layout {
        grid-template-columns: 1fr;
    }

    .tb-login-page .tb-left {
        min-height: 100vh;
        padding: 35px 20px 55px;
    }

    .tb-login-page .tb-card {
        max-width: 410px;
    }

    .tb-login-page .tb-right {
        display: none;
    }

    .tb-login-page .tb-heading {
        font-size: 31px;
    }
}

@media (max-width: 480px) {
    .tb-login-page .tb-left {
        padding-right: 18px;
        padding-left: 18px;
    }

    .tb-login-page .tb-brand {
        margin-bottom: 20px;
    }

    .tb-login-page .tb-brand-name {
        font-size: 23px;
    }

    .tb-login-page .tb-heading {
        font-size: 28px;
    }

    .tb-login-page .tb-subtitle {
        font-size: 12px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .tb-login-page *,
    .tb-login-page *::before,
    .tb-login-page *::after {
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: .01ms !important;
        scroll-behavior: auto !important;
    }
}
</style>
@endpush

@section('body')
<div class="tb-login-page">
    <div class="tb-layout">

        {{-- LEFT: LOGIN FORM --}}
        <main class="tb-left">
            <div class="tb-card">

                <a href="{{ url('/') }}" class="tb-brand">
                    <span class="tb-brand-icon">T</span>
                    <span class="tb-brand-name">
                        TYPING<span>MOJA</span>
                    </span>
                </a>

                <div class="tb-kicker">
                    <span class="tb-kicker-dot"></span>
                    আপনার টাইপিং যাত্রা শুরু হোক
                </div>

                <h1 class="tb-heading">
                    আবার স্বাগতম! 👋
                </h1>

                <p class="tb-subtitle">
                    আপনার অ্যাকাউন্টে লগইন করে বাংলা ও English
                    টাইপিং অনুশীলন চালিয়ে যান। নিয়মিত অনুশীলন করুন,
                    নিজের গতি বাড়ান এবং লক্ষ্যে পৌঁছান।
                </p>

                {{-- Session status --}}
                @if (session('status'))
                    <div class="tb-alert tb-alert-success" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Login errors --}}
                @if ($errors->any())
                    <div class="tb-alert" role="alert">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('login.attempt') }}"
                    id="tbLoginForm"
                >
                    @csrf

                    {{-- Email --}}
                    <div class="tb-field">
                        <svg class="tb-field-icon"
                             viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.8"
                             aria-hidden="true">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="m4 7 8 6 8-6"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>

                        <input
                            id="login"
                            type="text"
                            name="identity"
                            value="{{ old('login') }}"
                            placeholder=" "
                            autocomplete="username"
                            autocapitalize="none"
                            autocorrect="off"
                            spellcheck="false"
                            required
                            autofocus
                        >

                        <label for="login">
                            ইমেইল অথবা মোবাইল নম্বর
                        </label>
                    </div>

                    {{-- Password --}}
                    <div class="tb-field">
                        <svg class="tb-field-icon"
                             viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.8"
                             aria-hidden="true">
                            <rect x="4" y="10" width="16" height="10" rx="2"/>
                            <path d="M8 10V7a4 4 0 0 1 8 0v3"
                                  stroke-linecap="round"/>
                        </svg>

                        <input
                            id="tbPassword"
                            type="password"
                            name="password"
                            placeholder=" "
                            autocomplete="current-password"
                            required
                        >

                        <label for="tbPassword">
                            পাসওয়ার্ড
                        </label>

                        <button
                            type="button"
                            class="tb-eye"
                            id="tbTogglePassword"
                            aria-label="পাসওয়ার্ড দেখান"
                            aria-pressed="false"
                        >
                            <svg id="tbEyeIcon"
                                 width="19" height="19"
                                 viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.8"
                                 aria-hidden="true">
                                <path d="M2.5 12S5.5 5 12 5s9.5 7 9.5 7-3 7-9.5 7S2.5 12 2.5 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>

                    <div class="tb-options">
                        <label class="tb-remember">
                            <input type="checkbox" name="remember" value="1">
                            আমাকে মনে রাখুন
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">
                                পাসওয়ার্ড ভুলে গেছেন?
                            </a>
                        @endif
                    </div>

                    <button
                        type="submit"
                        class="tb-submit"
                        id="tbLoginButton"
                    >
                        <span class="tb-spinner" id="tbLoginSpinner"></span>

                        <svg width="18" height="18"
                             viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2"
                             aria-hidden="true">
                            <path d="M10 17l5-5-5-5"/>
                            <path d="M15 12H3"/>
                            <path d="M12 3h6a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3h-6"/>
                        </svg>

                        <span id="tbLoginButtonText">লগইন করুন</span>
                    </button>
                </form>

                {{-- Google login --}}
                <!-- @if (Route::has('auth.google.redirect'))
                    <div class="tb-divider">
                        <span></span>
                        <small>অথবা</small>
                        <span></span>
                    </div>

                    <a
                        href="{{ route('auth.google.redirect') }}"
                        class="tb-google"
                    >
                        <svg width="20" height="20"
                             viewBox="0 0 48 48"
                             aria-hidden="true">
                            <path fill="#FFC107"
                                  d="M43.6 20.5H42V20H24v8h11.3c-1.6 4.6-6 8-11.3 8-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.1 8 3l6-6C34.6 5.1 29.6 3 24 3 12.4 3 3 12.4 3 24s9.4 21 21 21 21-9.4 21-21c0-1.2-.1-2.3-.4-3.5z"/>
                            <path fill="#FF3D00"
                                  d="M6.3 14.7l6.6 4.8C14.6 15.9 18.9 13 24 13c3.1 0 5.8 1.1 8 3l6-6C34.6 5.1 29.6 3 24 3 16.3 3 9.6 7.3 6.3 14.7z"/>
                            <path fill="#4CAF50"
                                  d="M24 45c5.5 0 10.4-1.9 14.2-5.1l-6.6-5.6C29.5 36 26.9 37 24 37c-5.2 0-9.6-3.4-11.2-8l-6.6 5.1C9.5 40.6 16.2 45 24 45z"/>
                            <path fill="#1976D2"
                                  d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.2 4.2-4.1 5.6l6.6 5.6C41.9 36.4 45 30.8 45 24c0-1.2-.1-2.3-.4-3.5z"/>
                        </svg>
                        Google দিয়ে লগইন করুন
                    </a>

                    @unless (config('services.google.client_id'))
                        <p class="tb-google-note">
                            Google Login চালু করতে .env ফাইলে
                            GOOGLE_CLIENT_ID ও GOOGLE_CLIENT_SECRET সেট করুন।
                        </p>
                    @endunless
                @endif -->

                <p class="tb-register">
                    অ্যাকাউন্ট নেই?
                    <a href="{{ route('register') }}">রেজিস্টার করুন</a>
                </p>
            </div>

            <p class="tb-footer">
                © {{ date('Y') }} TypingMoja · সর্বস্বত্ব সংরক্ষিত
            </p>
        </main>

        {{-- RIGHT: TYPING PLATFORM SHOWCASE --}}
        <aside class="tb-right" id="tbShowcase">
            <canvas id="tbParticles" aria-hidden="true"></canvas>

            <div class="tb-orb tb-orb-one"></div>
            <div class="tb-orb tb-orb-two"></div>

            <div class="tb-showcase">

                <header class="tb-hero">
                    <div class="tb-hero-badge">
                        <span class="tb-live-dot"></span>
                        BANGLA & ENGLISH TYPING PLATFORM
                    </div>

                    <h2 class="hero-title">

                        টাইপিং শিখুন,

                        <span class="gradient">
                            গতি বাড়ান
                        </span>

                        এবং প্রতিযোগিতায় এগিয়ে থাকুন

                    </h2>

                    <p>
                        শুধু টাইপিং নয় — শিখুন, অনুশীলন করুন,
                        নিজের Speed ও Accuracy যাচাই করুন।
                        Basic থেকে Advanced হয়ে এগিয়ে যান পরবর্তী ধাপে।
                    </p>
                </header>

                {{-- Animated typing dashboard --}}
                <section class="tb-dashboard"
                         aria-label="টাইপিং অনুশীলনের ডেমো">

                    <div class="tb-dash-top">
                        <div class="tb-dash-brand">
                            <span class="tb-dash-logo">⌨</span>
                            <div>
                                <strong>TypingMoja Practice Lab</strong>
                                <small>আপনার দক্ষতা, আপনার অগ্রগতি</small>
                            </div>
                        </div>

                        <span class="tb-live">
                            <span class="tb-live-dot"></span>
                            LIVE DEMO
                        </span>
                    </div>

                    <div class="tb-stats">
                        <div class="tb-stat">
                            <small>Typing Speed</small>
                            <strong><span id="tbWpm">42</span> <span>WPM</span></strong>
                        </div>

                        <div class="tb-stat">
                            <small>Accuracy</small>
                            <strong><span id="tbAccuracy">98</span><span>%</span></strong>
                        </div>

                        <div class="tb-stat">
                            <small>Practice Time</small>
                            <strong><span id="tbTime">01:24</span></strong>
                        </div>

                        <div class="tb-stat">
                            <small>Progress</small>
                            <strong><span id="tbProgressText">68</span><span>%</span></strong>
                        </div>
                    </div>

                    <div class="tb-passage">
                        <div class="tb-passage-head">
                            <span> TYPING PRACTICE · DEMO CARD </span>
                            <b id="tbPassageLevel">BASIC</b>
                        </div>

                        <div class="tb-passage-text" aria-live="polite">
                            <span class="typed" id="tbTypedText"></span><span
                                class="current"
                                id="tbCurrentText"
                            ></span><span class="tb-caret"></span><span
                                id="tbRemainingText"
                            ></span>
                        </div>

                        <div class="tb-progress">
                            <span id="tbProgressBar"></span>
                        </div>
                    </div>

                    <div class="tb-keyboard" aria-hidden="true">
                        <div class="tb-key-row">
                            <span class="tb-key">Q</span>
                            <span class="tb-key">W</span>
                            <span class="tb-key">E</span>
                            <span class="tb-key">R</span>
                            <span class="tb-key">T</span>
                            <span class="tb-key">Y</span>
                            <span class="tb-key">U</span>
                            <span class="tb-key">I</span>
                            <span class="tb-key">O</span>
                            <span class="tb-key">P</span>
                        </div>
                        <div class="tb-key-row">
                            <span class="tb-key">A</span>
                            <span class="tb-key">S</span>
                            <span class="tb-key">D</span>
                            <span class="tb-key">F</span>
                            <span class="tb-key">G</span>
                            <span class="tb-key">H</span>
                            <span class="tb-key">J</span>
                            <span class="tb-key">K</span>
                            <span class="tb-key">L</span>
                        </div>
                        <div class="tb-key-row">
                            <span class="tb-key">Z</span>
                            <span class="tb-key">X</span>
                            <span class="tb-key">C</span>
                            <span class="tb-key">V</span>
                            <span class="tb-key">B</span>
                            <span class="tb-key">N</span>
                            <span class="tb-key">M</span>
                            <span class="tb-key">⌫</span>
                        </div>
                        <div class="tb-key-row">
                            <span class="tb-key space">SPACE</span>
                        </div>
                    </div>

                    <div class="tb-dash-bottom">
                        <span>⌨ বাংলা ও English — দুই ভাষায় অনুশীলন</span>
                        <strong>🎯 Smart Progress</strong>
                    </div>
                </section>

                {{-- Language switch --}}
                <div style="display:flex;justify-content:flex-end;
                            margin:0 auto 12px;max-width:790px">
                    <div class="tb-language" aria-label="ডেমোর ভাষা নির্বাচন">
                        <button type="button"
                                class="active"
                                data-language="bn"
                                aria-pressed="true">
                            বাংলা
                        </button>
                        <button type="button"
                                data-language="en"
                                aria-pressed="false">
                            English
                        </button>
                    </div>
                </div>

                {{-- Learning path --}}
                <section aria-label="টাইপিং শেখার ধাপ">
                    <div class="tb-path-heading">
                        <span>🚀 আপনার Learning Path</span>
                        <span>শুরু → অনুশীলন → দক্ষতা</span>
                    </div>

                    <div class="tb-levels">
                        <div class="tb-level">
                            <span class="tb-level-icon">🌱</span>
                            <div>
                                <strong>Basic</strong>
                                <small>শুরু করুন · ১ ও ৩ মিনিট</small>
                            </div>
                        </div>

                        <div class="tb-level">
                            <span class="tb-level-icon">📈</span>
                            <div>
                                <strong>Advanced</strong>
                                <small>দক্ষতা বাড়ান · ৩ ও ৫ মিনিট</small>
                            </div>
                        </div>

                        <div class="tb-level">
                            <span class="tb-level-icon">🔥</span>
                            <div>
                                <strong>Hard</strong>
                                <small>চ্যালেঞ্জ নিন · ৩–১৫ মিনিট</small>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="tb-features">
                    <div class="tb-feature">
                        <strong>⚡ WPM</strong>
                        <small>টাইপিং স্পিড</small>
                    </div>
                    <div class="tb-feature">
                        <strong>🎯 Accuracy</strong>
                        <small>নির্ভুলতা</small>
                    </div>
                    <div class="tb-feature">
                        <strong>🌐 Dual Language</strong>
                        <small>বাংলা + English</small>
                    </div>
                    <div class="tb-feature">
                        <strong>🏆 Progress</strong>
                        <small>দক্ষতার অগ্রগতি</small>
                    </div>
                </div>

            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const root = document.querySelector('.tb-login-page');
    if (!root) return;

    /* Password visibility */
    const password = root.querySelector('#tbPassword');
    const toggle = root.querySelector('#tbTogglePassword');

    if (password && toggle) {
        toggle.addEventListener('click', function () {
            const show = password.type === 'password';
            password.type = show ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', String(show));
            toggle.setAttribute(
                'aria-label',
                show ? 'পাসওয়ার্ড লুকান' : 'পাসওয়ার্ড দেখান'
            );
        });
    }

    /* Login loading state */
    const form = root.querySelector('#tbLoginForm');
    const submit = root.querySelector('#tbLoginButton');
    const spinner = root.querySelector('#tbLoginSpinner');
    const buttonText = root.querySelector('#tbLoginButtonText');

    if (form && submit) {
        form.addEventListener('submit', function () {
            if (!form.checkValidity()) return;

            submit.disabled = true;
            if (spinner) spinner.style.display = 'inline-block';
            if (buttonText) buttonText.textContent = 'লগইন হচ্ছে...';
        });
    }

    /* Typing dashboard demo */
    const passages = {
        bn: 'নিয়মিত টাইপিং অনুশীলন করলে গতি ও নির্ভুলতা বাড়ে। প্রতিদিন অনুশীলন করুন এবং নিজের লক্ষ্য অর্জন করুন।',
        en: 'Practice typing every day to improve your speed and accuracy. Keep learning and achieve your goals.'
    };

    const typedEl = root.querySelector('#tbTypedText');
    const currentEl = root.querySelector('#tbCurrentText');
    const remainingEl = root.querySelector('#tbRemainingText');
    const progressBar = root.querySelector('#tbProgressBar');
    const progressText = root.querySelector('#tbProgressText');
    const accuracyEl = root.querySelector('#tbAccuracy');
    const wpmEl = root.querySelector('#tbWpm');
    const timeEl = root.querySelector('#tbTime');
    const levelEl = root.querySelector('#tbPassageLevel');
    const languageButtons = root.querySelectorAll('[data-language]');

    let language = 'bn';
    let position = 0;
    let seconds = 84;
    let tick = 0;

    function renderPassage() {
        const passage = passages[language];
        position = position % passage.length;

        if (typedEl) typedEl.textContent = passage.slice(0, position);
        if (currentEl) currentEl.textContent = passage[position] || '';
        if (remainingEl) remainingEl.textContent = passage.slice(position + 1);

        const progress = Math.round((position / passage.length) * 100);

        if (progressBar) progressBar.style.width = progress + '%';
        if (progressText) progressText.textContent = progress;
        if (levelEl) levelEl.textContent = progress >= 70 ? 'ADVANCED' : 'BASIC';
    }

    languageButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            language = button.dataset.language === 'en' ? 'en' : 'bn';

            languageButtons.forEach(function (item) {
                const active = item === button;
                item.classList.toggle('active', active);
                item.setAttribute('aria-pressed', String(active));
            });

            position = 0;
            renderPassage();
        });
    });

    const keys = Array.from(root.querySelectorAll('.tb-key:not(.space)'));
    let keyIndex = 0;

    function animateKey() {
        if (!keys.length) return;

        keys.forEach(function (key) {
            key.classList.remove('active');
        });

        keys[keyIndex].classList.add('active');
        keyIndex = (keyIndex + 1) % keys.length;
    }

    const reducedMotion = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    renderPassage();

    if (!reducedMotion) {
        window.setInterval(function () {
            position++;

            if (position >= passages[language].length) {
                position = 0;
            }

            tick++;
            renderPassage();
            animateKey();

            if (wpmEl) {
                wpmEl.textContent = String(40 + (tick % 7));
            }

            if (accuracyEl) {
                accuracyEl.textContent = String(97 + (tick % 3));
            }

            seconds = seconds > 0 ? seconds - 1 : 84;

            if (timeEl) {
                const mins = String(Math.floor(seconds / 60)).padStart(2, '0');
                const secs = String(seconds % 60).padStart(2, '0');
                timeEl.textContent = mins + ':' + secs;
            }
        }, 650);
    }

    /* Lightweight animated particles */
    const canvas = root.querySelector('#tbParticles');
    const panel = root.querySelector('#tbShowcase');

    if (!canvas || !panel || reducedMotion) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    let width = 0;
    let height = 0;
    let particles = [];
    let animationId = null;

    function resizeCanvas() {
        const rect = panel.getBoundingClientRect();
        const dpr = Math.min(window.devicePixelRatio || 1, 1.5);

        width = rect.width;
        height = rect.height;

        canvas.width = Math.max(1, Math.floor(width * dpr));
        canvas.height = Math.max(1, Math.floor(height * dpr));

        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        const count = Math.min(45, Math.max(18, Math.floor(width / 20)));

        particles = Array.from({ length: count }, function () {
            return {
                x: Math.random() * width,
                y: Math.random() * height,
                vx: (Math.random() - 0.5) * 0.25,
                vy: (Math.random() - 0.5) * 0.25,
                r: 0.7 + Math.random() * 1.2
            };
        });
    }

    function drawParticles() {
        ctx.clearRect(0, 0, width, height);

        particles.forEach(function (p) {
            p.x += p.vx;
            p.y += p.vy;

            if (p.x < 0 || p.x > width) p.vx *= -1;
            if (p.y < 0 || p.y > height) p.vy *= -1;

            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(147,197,253,.55)';
            ctx.fill();
        });

        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const a = particles[i];
                const b = particles[j];
                const dx = a.x - b.x;
                const dy = a.y - b.y;
                const distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < 105) {
                    ctx.beginPath();
                    ctx.moveTo(a.x, a.y);
                    ctx.lineTo(b.x, b.y);
                    ctx.strokeStyle =
                        'rgba(96,165,250,' + ((1 - distance / 105) * 0.16) + ')';
                    ctx.lineWidth = 1;
                    ctx.stroke();
                }
            }
        }

        animationId = window.requestAnimationFrame(drawParticles);
    }

    resizeCanvas();
    drawParticles();

    let resizeTimeout;

    window.addEventListener('resize', function () {
        window.clearTimeout(resizeTimeout);

        resizeTimeout = window.setTimeout(function () {
            if (animationId) window.cancelAnimationFrame(animationId);
            resizeCanvas();
            drawParticles();
        }, 150);
    });

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            if (animationId) window.cancelAnimationFrame(animationId);
            animationId = null;
        } else if (!animationId) {
            drawParticles();
        }
    });
})();
</script>
@endpush
