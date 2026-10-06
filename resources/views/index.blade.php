<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <!-- Основное -->
  <title>VibeCheck — афиша событий и поиск компании</title>
  <meta name="description"
        content="VibeCheck: афиша событий рядом, поиск компании (+1), свои встречи и чаты ивентов. Слови вайб своего города!">
  <link rel="canonical" href="https://vibecheck.example/">
  <meta name="theme-color" content="#0c0a2e">
  <meta name="color-scheme" content="dark">

  <!-- Фавикон -->
  <link rel="icon" href="/images/icon.jpg" sizes="48x48">

  <!-- Open Graph (Telegram, VK, WhatsApp, Facebook) -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="VibeCheck">
  <meta property="og:title" content="VibeCheck — афиша событий и поиск компании">
  <meta property="og:description"
        content="События рядом, компания для любого отдыха и чаты ивентов. Слови вайб своего города!">
  <meta property="og:url" content="{{ request()->fullUrl() }}">
  <meta property="og:image" content="{{ asset('images/cover.jpg') }}">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="VibeCheck. Events. Company. Vibes.">
  <meta property="og:locale" content="ru_RU">

  <!-- Twitter / X -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="VibeCheck — афиша событий и поиск компании">
  <meta name="twitter:description" content="События рядом, компания для любого отдыха и чаты ивентов.">
  <meta name="twitter:image" content="{{ asset('images/cover.jpg') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;800&display=swap">
  <style>
    :root {
      box-sizing: border-box;
      --bg: #0c0a2e;
      --bg2: #15104a;
      --ink: #f3f1ff;
      --mute: #aaa6d6;
      --pink: #ff4fe6;
      --cyan: #38e1ff;
      --violet: #8a4dff;
      --line: rgba(160, 150, 255, .22);
      --g: linear-gradient(90deg, var(--pink), var(--cyan));
      padding-top: env(safe-area-inset-top, 0px);
      padding-bottom: env(safe-area-inset-bottom, 0px)
    }

    *, *::before, *::after {
      box-sizing: inherit
    }

    html {
      color-scheme: dark;
      scroll-behavior: smooth
    }

    body {
      margin: 0;
      background: var(--bg);
      color: var(--ink);
      font: 400 17px/1.6 Montserrat, system-ui, sans-serif
    }

    body::before {
      content: "";
      position: fixed;
      inset: 0;
      z-index: -1;
      background: radial-gradient(60% 50% at 85% 0, rgba(138, 77, 255, .35), transparent), radial-gradient(50% 40% at 0 30%, rgba(56, 225, 255, .14), transparent)
    }

    a {
      color: inherit
    }

    :focus-visible {
      outline: 3px solid var(--cyan);
      outline-offset: 3px;
      border-radius: 8px
    }

    .w {
      max-width: 1040px;
      margin: 0 auto;
      padding: 0 22px
    }

    header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 18px 0
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 800;
      letter-spacing: .04em;
      text-decoration: none
    }

    .brand img {
      width: 40px;
      height: 40px;
      border-radius: 11px
    }

    header nav a {
      margin-left: 22px;
      color: var(--mute);
      text-decoration: none;
      font-weight: 500;
      font-size: 15px
    }

    header nav a:hover {
      color: var(--ink)
    }

    .hero {
      padding: 48px 0 40px
    }

    .dev {
      position: relative;
      justify-self: center;
      width: 300px;
      padding: 10px;
      border-radius: 52px;
      background: linear-gradient(145deg, #8a8fb8, #2b2c4a 28%, #12122a 62%, #55598a);
      box-shadow: inset 0 0 0 1.5px rgba(255, 255, 255, .22), 0 0 0 1px #05051a, 0 50px 90px rgba(0, 0, 0, .65), 0 0 100px rgba(138, 77, 255, .45);
      animation: fl 6s ease-in-out infinite
    }

    .dev::before, .dev::after {
      content: "";
      position: absolute;
      width: 4px;
      border-radius: 3px;
      background: linear-gradient(#5a5e8c, #24253f)
    }

    .dev::before {
      right: -4px;
      top: 150px;
      height: 76px
    }

    .dev::after {
      left: -4px;
      top: 120px;
      height: 116px
    }

    .bezel {
      padding: 5px;
      border-radius: 43px;
      background: #000
    }

    .screen {
      position: relative;
      border-radius: 38px;
      overflow: hidden;
      background: #212226
    }

    .sb {
      position: relative;
      height: 36px;
      padding: 9px 24px 0;
      display: flex;
      justify-content: space-between;
      color: #fff;
      font: 600 12px/1 Montserrat, system-ui, sans-serif;
      background: #212226
    }

    .sb svg {
      height: 11px;
      width: auto;
      fill: #fff
    }

    .sb span {
      display: flex;
      gap: 5px;
      align-items: center
    }

    .cam {
      position: absolute;
      top: 11px;
      left: 50%;
      width: 12px;
      height: 12px;
      margin-left: -6px;
      border-radius: 50%;
      background: radial-gradient(circle at 35% 35%, #2b3a66, #05060f 60%);
      box-shadow: 0 0 0 2px #000
    }

    .shot {
      display: block;
      width: 100%;
      height: auto
    }

    .glare {
      position: absolute;
      inset: 0;
      pointer-events: none;
      background: linear-gradient(115deg, rgba(255, 255, 255, .13), rgba(255, 255, 255, 0) 32%)
    }

    .pill {
      position: absolute;
      bottom: 8px;
      left: 50%;
      width: 96px;
      height: 4px;
      margin-left: -48px;
      border-radius: 3px;
      background: rgba(255, 255, 255, .75)
    }

    @keyframes fl {
      50% {
        transform: translateY(-10px)
      }
    }

    .hero {
      display: grid;
      grid-template-columns:1.15fr .85fr;
      gap: 40px;
      align-items: center;
      text-align: left
    }

    .hero h1 {
      margin-left: 0
    }

    .hero .lead {
      margin-left: 0
    }

    @media (max-width: 760px) {
      .hero {
        grid-template-columns:1fr;
        text-align: center
      }

      .hero h1, .hero .lead {
        margin-left: auto
      }

      .dev {
        width: 268px
      }
    }

    h1 {
      font-weight: 800;
      font-size: clamp(36px, 7vw, 68px);
      line-height: 1.05;
      margin: 0 auto 20px;
      max-width: 15ch;
      background: var(--g);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent
    }

    .lead {
      max-width: 56ch;
      margin: 0 auto 32px;
      color: var(--mute);
      font-size: clamp(17px, 2.4vw, 20px)
    }

    .play {
      display: inline-flex;
      align-items: center;
      gap: 14px;
      padding: 12px 26px 12px 20px;
      border-radius: 16px;
      background: #fff;
      color: #14103d;
      text-decoration: none;
      font-weight: 800;
      box-shadow: 0 0 36px rgba(255, 79, 230, .45)
    }

    .play svg {
      width: 30px;
      height: 30px
    }

    .play small {
      display: block;
      font-size: 11px;
      font-weight: 500;
      line-height: 1.2
    }

    .play span {
      display: block;
      font-size: 19px;
      line-height: 1.2
    }

    .play:hover {
      transform: translateY(-2px)
    }

    .cover {
      margin: 48px auto 0;
      border-radius: 24px;
      border: 1px solid var(--line);
      box-shadow: 0 0 80px rgba(138, 77, 255, .35);
      display: block;
      width: 100%;
      height: auto
    }

    section {
      padding: 72px 0 0
    }

    h2 {
      font-size: clamp(26px, 4.5vw, 40px);
      line-height: 1.15;
      margin: 0 0 12px;
      font-weight: 800
    }

    .sub {
      color: var(--mute);
      margin: 0 0 36px;
      max-width: 60ch
    }

    .feat {
      display: grid;
      grid-template-columns:repeat(6, 1fr);
      gap: 16px
    }

    .f {
      grid-column: span 2;
      padding: 24px;
      border-radius: 20px;
      background: linear-gradient(160deg, rgba(138, 77, 255, .18), rgba(21, 16, 74, .6));
      border: 1px solid var(--line)
    }

    .f:nth-child(1), .f:nth-child(2) {
      grid-column: span 3
    }

    .f h3 {
      margin: 0 0 8px;
      font-size: 19px
    }

    .f p {
      margin: 0;
      color: var(--mute);
      font-size: 15.5px
    }

    .f i {
      display: block;
      font-style: normal;
      font-size: 26px;
      margin-bottom: 12px
    }

    .aud {
      display: grid;
      grid-template-columns:repeat(2, 1fr);
      gap: 14px;
      padding: 0;
      margin: 0;
      list-style: none
    }

    .aud li {
      padding: 18px 22px;
      border-left: 4px solid var(--pink);
      background: rgba(255, 255, 255, .04);
      border-radius: 0 14px 14px 0
    }

    .aud li:nth-child(2) {
      border-color: var(--violet)
    }

    .aud li:nth-child(3) {
      border-color: var(--cyan)
    }

    .aud li:nth-child(4) {
      border-color: var(--pink)
    }

    .cta {
      margin-top: 80px;
      padding: 56px 24px;
      text-align: center;
      border-radius: 28px;
      background: linear-gradient(135deg, rgba(255, 79, 230, .28), rgba(56, 225, 255, .2));
      border: 1px solid var(--line)
    }

    .cta h2 {
      max-width: 22ch;
      margin: 0 auto 12px
    }

    .cta p {
      color: var(--mute);
      max-width: 46ch;
      margin: 0 auto 28px
    }

    footer {
      padding: 48px 0 40px;
      margin-top: 24px;
      color: var(--mute);
      font-size: 14px;
      display: flex;
      flex-wrap: wrap;
      gap: 12px 28px;
      justify-content: space-between;
      align-items: center
    }

    footer a {
      text-decoration: none;
      margin-right: 20px
    }

    footer a:hover {
      color: var(--ink);
      text-decoration: underline
    }

    @media (max-width: 760px) {
      header nav {
        display: none
      }

      .f, .f:nth-child(1), .f:nth-child(2) {
        grid-column: span 6
      }

      .aud {
        grid-template-columns:1fr
      }

      section {
        padding-top: 56px
      }

      footer {
        flex-direction: column;
        align-items: flex-start
      }
    }

    @media (prefers-reduced-motion: reduce) {
      * {
        animation: none !important;
        transition: none !important;
        scroll-behavior: auto !important
      }
    }

    .play {
      transition: transform .2s
    }
  </style>
</head>
<body>
<div class="w">
  <header>
    <a class="brand" href="#top"><img src="images/icon.jpg" alt="Логотип VibeCheck">VIBECHECK</a>
    <nav><a href="#features">Возможности</a><a href="#for">Для кого</a><a href="#download">Скачать</a></nav>
  </header>

  <main id="top">
    <div class="hero">
      <div class="txt">
        <h1>Слови вайб своего города</h1>
        <p class="lead">Афиша событий рядом и простой способ найти компанию для любого отдыха: от концерта до настолок
          во дворе.</p>
        <a class="play" id="download" href="https://play.google.com/store/apps/details?id=com.vibecheck.app"
           rel="noopener">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M3.6 1.8 14 12 3.6 22.2c-.4-.3-.6-.8-.6-1.4V3.2c0-.6.2-1.1.6-1.4Z" fill="#38e1ff"/>
            <path d="m17.4 8.6-3.4 3.4L3.6 1.8c.5-.3 1.1-.3 1.7 0l12.1 6.8Z" fill="#6ee87a"/>
            <path d="m17.4 15.4-12.1 6.8c-.6.3-1.2.3-1.7 0L14 12l3.4 3.4Z" fill="#ff4f6a"/>
            <path d="m21 10.2-3.6-1.6L14 12l3.4 3.4 3.6-1.6c1.3-.8 1.3-2.8 0-3.6Z" fill="#ffd23f"/>
          </svg>
          <div><small>Доступно в</small><span>Google Play</span></div>
        </a></div>
      <div class="dev">
        <div class="bezel">
          <div class="screen">
            <div class="sb"><b>9:41</b><i class="cam"></i><span><svg viewBox="0 0 18 11"><path
                          d="M0 8h3v3H0zM5 5h3v6H5zM10 2.5h3V11h-3zM15 0h3v11h-3z"/></svg><svg viewBox="0 0 22 11"><rect
                          x=".5" y=".5" width="18" height="10" rx="2.5" fill="none" stroke="#fff"/><rect x="2" y="2"
                                                                                                         width="12"
                                                                                                         height="7"
                                                                                                         rx="1.2"/><rect
                          x="19.5" y="3.5" width="2" height="4" rx="1"/></svg></span></div>
            <img class="shot" src="images/shot.jpg"
                 alt="Экран события в приложении VibeCheck: джазовый вечер, карта и чат" width="560" height="1151">
            <div class="pill"></div>
            <div class="glare"></div>
          </div>
        </div>
      </div>
    </div>
    <img class="cover" src="images/cover.jpg" alt="VibeCheck. Events. Company. Vibes." width="1200" height="580">

    <section id="features">
      <h2>Всё, чтобы вечер удался</h2>
      <p class="sub">Не знаешь, как провести вечер или с кем сходить на концерт? VibeCheck подскажет и соберёт тусовку
        за пару кликов.</p>
      <div class="feat">
        <div class="f"><i>🎟️</i>
          <h3>Афиша и ивенты рядом</h3>
          <p>Концерты, вечеринки, мастер-классы, настольные игры, стендап и ламповые домашние посиделки.</p></div>
        <div class="f"><i>🤝</i>
          <h3>Поиск компании (+1)</h3>
          <p>Ищешь, с кем сходить в кино, на выставку или на тренировку? Находи единомышленников со схожими
            интересами.</p></div>
        <div class="f"><i>⚽</i>
          <h3>Создавай свои встречи</h3>
          <p>Турнир по футболу, настолки во дворе или масштабная тусовка: создай ивент и пригласи участников.</p></div>
        <div class="f"><i>📍</i>
          <h3>Поиск на карте</h3>
          <p>События и активные компании прямо вокруг тебя в режиме реального времени.</p></div>
        <div class="f"><i>💬</i>
          <h3>Живое общение</h3>
          <p>Обсуждай детали в чатах ивентов, знакомься и заводи новых друзей.</p></div>
      </div>
    </section>

    <section id="for">
      <h2>Для кого VibeCheck</h2>
      <p class="sub">Для всех, кто любит, когда вокруг что-то происходит.</p>
      <ul class="aud">
        <li>Для тех, кто не хочет проводить выходные дома</li>
        <li>Для любителей активного отдыха, спорта и совместных прогулок</li>
        <li>Для тех, кто переехал в новый город и хочет найти классное окружение</li>
        <li>Для организаторов локальных встреч и тусовок</li>
      </ul>
    </section>

    <div class="cta">
      <h2>Не пропускай самые яркие события вокруг</h2>
      <p>Скачивай VibeCheck, находи компанию и лови правильный вайб.</p>
      <a class="play" href="https://play.google.com/store/apps/details?id=com.vibecheck.app" rel="noopener">
        <svg viewBox="0 0 24 24" aria-hidden="true">
          <path d="M3.6 1.8 14 12 3.6 22.2c-.4-.3-.6-.8-.6-1.4V3.2c0-.6.2-1.1.6-1.4Z" fill="#38e1ff"/>
          <path d="m17.4 8.6-3.4 3.4L3.6 1.8c.5-.3 1.1-.3 1.7 0l12.1 6.8Z" fill="#6ee87a"/>
          <path d="m17.4 15.4-12.1 6.8c-.6.3-1.2.3-1.7 0L14 12l3.4 3.4Z" fill="#ff4f6a"/>
          <path d="m21 10.2-3.6-1.6L14 12l3.4 3.4 3.6-1.6c1.3-.8 1.3-2.8 0-3.6Z" fill="#ffd23f"/>
        </svg>
        <div><small>Доступно в</small><span>Google Play</span></div>
      </a>
    </div>
  </main>

  <footer>
    <div><a href="terms">Пользовательское соглашение</a><a href="privacy">Политика конфиденциальности</a></div>
    <div>© 2026 VibeCheck. Events. Company. Vibes.</div>
  </footer>
</div>
</body>
</html>
