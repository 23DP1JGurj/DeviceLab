<template>
  <div class="landing" ref="root">
<!-- ===== Mobile sheet ===== -->
  <div class="sheet" id="sheet" aria-hidden="true">
    <div class="sheet-panel" role="dialog" aria-modal="true" aria-label="Izvēlne">
      <div class="sheet-head">
        <b>Izvēlne</b>
        <button class="btn" id="closeSheet" style="padding:10px 12px;">Aizvērt</button>
      </div>
      <div class="sheet-links">
        <a href="#services">Pakalpojumi</a>
        <a href="#how">Kā tas darbojas</a>
        <a href="#prices">Cenas</a>
        <a href="#reviews">Atsauksmes</a>
        <a href="#faq">FAQ</a>
        <a href="#contacts">Kontakti</a>
        <a href="/orders" style="background: rgba(255,255,255,.08);">Noformēt pieteikumu</a>
      </div>
    </div>
  </div>

  <!-- ===== Header ===== -->
  <header class="header" id="header">
    <div class="container">
      <div class="header-row">
        <a class="brand" href="#top">
          <svg viewBox="0 0 48 48" fill="none" aria-hidden="true">
            <path d="M24 6c4.9 0 8.4 3.5 8.4 8.4 0 1.1-.2 2.1-.6 3.1 1-.4 2-.6 3.1-.6 4.9 0 8.4 3.5 8.4 8.4S39.8 34 34.9 34c-1.1 0-2.1-.2-3.1-.6.4 1 .6 2 .6 3.1 0 4.9-3.5 8.4-8.4 8.4s-8.4-3.5-8.4-8.4c0-1.1.2-2.1.6-3.1-1 .4-2 .6-3.1.6C8.2 34 4.7 30.5 4.7 25.6S8.2 17.2 13.1 17.2c1.1 0 2.1.2 3.1.6-.4-1-.6-2-.6-3.1C15.6 9.5 19.1 6 24 6Z" fill="rgba(255,255,255,.92)"/>
          </svg>
          <span>DeviceLab</span>
        </a>

        <nav class="nav" aria-label="Primary">
          <a href="#services">Pakalpojumi</a>
          <a href="#how">Kā tas darbojas</a>
          <a href="#prices">Cenas</a>
          <a href="#reviews">Atsauksmes</a>
          <a href="#faq">FAQ</a>
          <a href="#contacts">Kontakti</a>
        </nav>

        <div class="cta">
          <button class="btn burger" id="burger" aria-label="Atvērt izvēlni">☰</button>
          <a class="btn primary" href="/orders">Noformēt pieteikumu</a>
        </div>
      </div>
    </div>
  </header>

  <!-- ===== Hero ===== -->
  <section class="hero" id="top">
    <svg class="hero-lines" viewBox="0 0 1200 800" preserveAspectRatio="none" aria-hidden="true">
      <path d="M-50,560 C200,430 380,690 620,560 C860,430 980,640 1250,520" stroke="rgba(255,255,255,.28)" stroke-width="2" fill="none"/>
      <path d="M-80,610 C180,480 400,740 640,610 C880,480 980,690 1260,570" stroke="rgba(255,255,255,.18)" stroke-width="2" fill="none"/>
      <path d="M-100,660 C160,530 420,790 660,660 C900,530 980,740 1280,620" stroke="rgba(255,255,255,.14)" stroke-width="2" fill="none"/>
    </svg>

    <!-- floating decor -->
    <svg class="float one" viewBox="0 0 320 220" aria-hidden="true">
      <path d="M85 80c0-45 35-74 75-74s75 29 75 74" stroke="rgba(255,255,255,.9)" stroke-width="12" stroke-linecap="round" fill="none"/>
      <rect x="55" y="86" width="48" height="74" rx="18" fill="rgba(255,255,255,.92)"/>
      <rect x="217" y="86" width="48" height="74" rx="18" fill="rgba(255,255,255,.92)"/>
      <path d="M106 176c18 12 36 18 54 18 19 0 37-6 55-18" stroke="rgba(255,255,255,.22)" stroke-width="10" stroke-linecap="round" fill="none"/>
    </svg>

    <svg class="float two" viewBox="0 0 220 220" aria-hidden="true">
      <rect x="50" y="30" width="120" height="160" rx="60" fill="rgba(255,255,255,.92)"/>
      <path d="M110 48v36" stroke="rgba(16,51,110,.35)" stroke-width="10" stroke-linecap="round"/>
      <path d="M50 110h120" stroke="rgba(16,51,110,.10)" stroke-width="2"/>
    </svg>

    <svg class="float three" viewBox="0 0 560 360" overflow="visible" aria-hidden="true">
      <g transform="translate(100,55) rotate(-14)">
        <rect x="0" y="0" width="420" height="260" rx="30" fill="rgba(255,255,255,.92)"/>
        <rect x="18" y="18" width="384" height="224" rx="22" fill="rgba(240,244,255,1)"/>
        <g fill="rgba(16,51,110,.10)">
          <rect x="34" y="38" width="44" height="26" rx="7"/>
          <rect x="84" y="38" width="44" height="26" rx="7"/>
          <rect x="134" y="38" width="44" height="26" rx="7"/>
          <rect x="184" y="38" width="44" height="26" rx="7"/>
          <rect x="234" y="38" width="44" height="26" rx="7"/>
          <rect x="284" y="38" width="44" height="26" rx="7"/>
          <rect x="334" y="38" width="52" height="26" rx="7"/>
          <rect x="34" y="72" width="54" height="26" rx="7"/>
          <rect x="94" y="72" width="44" height="26" rx="7"/>
          <rect x="144" y="72" width="44" height="26" rx="7"/>
          <rect x="194" y="72" width="44" height="26" rx="7"/>
          <rect x="244" y="72" width="44" height="26" rx="7"/>
          <rect x="294" y="72" width="44" height="26" rx="7"/>
          <rect x="344" y="72" width="42" height="26" rx="7"/>
          <rect x="34" y="106" width="64" height="26" rx="7"/>
          <rect x="104" y="106" width="44" height="26" rx="7"/>
          <rect x="154" y="106" width="44" height="26" rx="7"/>
          <rect x="204" y="106" width="44" height="26" rx="7"/>
          <rect x="254" y="106" width="44" height="26" rx="7"/>
          <rect x="304" y="106" width="44" height="26" rx="7"/>
          <rect x="354" y="106" width="32" height="26" rx="7"/>
          <rect x="34" y="140" width="74" height="26" rx="7"/>
          <rect x="114" y="140" width="44" height="26" rx="7"/>
          <rect x="164" y="140" width="44" height="26" rx="7"/>
          <rect x="214" y="140" width="44" height="26" rx="7"/>
          <rect x="264" y="140" width="44" height="26" rx="7"/>
          <rect x="314" y="140" width="72" height="26" rx="7"/>
          <rect x="120" y="186" width="200" height="30" rx="10" fill="rgba(47,124,255,.18)"/>
          <rect x="160" y="222" width="120" height="14" rx="7" fill="rgba(16,51,110,.10)"/>
        </g>
      </g>
    </svg>

    <div class="container">
      <div class="hero-grid">
        <div>
          <div class="kicker">SERVISA CENTRS • TEHNIKAS REMONTS</div>
          <h1>DeviceLab</h1>
          <p class="lead">Tehnikas remonts Rīgā — uzticams serviss netālu.</p>

          <div class="hero-actions">
            <a class="btn light" href="/orders">Noformēt pieteikumu</a>
          </div>

          <!-- Noņemts garais teksts un ikonu bloki, lai pirmais ekrāns būtu lakonisks -->
        </div>

        <div class="mockwrap">
          <div class="mock" role="img" aria-label="Pieteikuma noformēšanas piemērs">
            <div class="mock-inner">
              <div class="mock-topbar">
                <div class="dots" aria-hidden="true">
                  <span class="dot"></span><span class="dot"></span><span class="dot"></span>
                </div>
                <div style="opacity:.7;font-weight:700;">devicelab.lv</div>
              </div>

              <!-- DEMO (nav īsta forma) -->
              <div class="demo-card demo-only">
                <div>
                  <div class="demo-title">Pieteikumu noformēt — vienkārši</div>
                  <!-- Aizpildījuma piemērs. Nākotnē poga zemāk vedīs uz īstu pasūtījuma noformēšanu. -->
                  <div class="demo-sub">Aizpildi datus — un mēs ar tevi sazināsimies.</div>
                </div>

                <div class="grid2">
                  <div class="field">
                    <label for="dname">Vārds</label>
                    <input class="input" id="dname" type="text" value="Aleksejs Ivanovs" disabled />
                  </div>
                  <div class="field">
                    <label for="dphone">Tālrunis</label>
                    <input class="input" id="dphone" type="text" value="+371 2000 1234" disabled />
                  </div>
                </div>

                <div class="grid2">
                  <div class="field">
                    <label for="dtype">Ierīces tips</label>
                    <select class="select" id="dtype" disabled>
                      <option selected>Telefons</option>
                      <option>Portatīvais dators</option>
                      <option>Planšete</option>
                      <option>Cits</option>
                    </select>
                  </div>

                  <div class="field">
                    <label for="doffice">Filiāle (lokācija)</label>
                    <select class="select" id="doffice" disabled>
                      <option selected>Rīga — Centrs</option>
                      <option>Rīga — Purvciems</option>
                    </select>
                  </div>
                </div>

                <div class="field">
                  <label for="dissue">Problēmas apraksts</label>
                  <textarea class="textarea" id="dissue" disabled>Neieslēdzas pēc uzlādes. Dažreiz mirgo logo un atkal izslēdzas.</textarea>
                </div>

                <div class="demo-actions">
                  <a class="btn dark" href="/orders" id="goOrder">Pāriet uz pieteikuma noformēšanu</a>
                  <!-- Šī ir demonstrācija. Reālo nosūtīšanu pieslēgsim vēlāk. -->
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <svg class="wave" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path d="M0,64 C240,10 480,110 720,64 C960,18 1200,110 1440,64 L1440,120 L0,120 Z" fill="#f6f8fc"/>
    </svg>
  </section>

  <!-- ===== Services ===== -->
  <section class="section services" id="services">
    <div class="container">
      <h2 class="section-title">Pakalpojumi</h2>
      <p class="section-lead">
        Remontu veicam rūpīgi un godīgi: izskaidrojam bojājuma cēloni, piedāvājam variantus, saskaņojam cenu.
        Zemāk — populārākie pakalpojumi. Ja neatrodi savu problēmu — uzraksti, palīdzēsim.
      </p>

      <div class="services-grid">
        <article class="card">
          <div class="iconbox" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
              <path d="M4 6h16v12H4z" stroke="rgba(47,124,255,1)" stroke-width="2" />
              <path d="M8 18v2h8v-2" stroke="rgba(47,124,255,1)" stroke-width="2" />
            </svg>
          </div>
          <h3>Diagnostika</h3>
          <p>Pārbaudām ierīci un atrodam cēloni. Pirms remonta informējam par cenu un termiņiem.</p>
        </article>

        <article class="card">
          <div class="iconbox" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
              <path d="M7 4h10v16H7z" stroke="rgba(47,124,255,1)" stroke-width="2"/>
              <path d="M9 7h6" stroke="rgba(47,124,255,1)" stroke-width="2"/>
              <path d="M10 20h4" stroke="rgba(47,124,255,1)" stroke-width="2"/>
            </svg>
          </div>
          <h3>Ekrāna maiņa</h3>
          <p>Plīsis stikls vai nestrādā skārienekrāns — piemeklēsim atbilstošu moduli un nomainīsim rūpīgi.</p>
        </article>

        <article class="card">
          <div class="iconbox" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
              <path d="M7 7h10v10H7z" stroke="rgba(47,124,255,1)" stroke-width="2"/>
              <path d="M9 3v4m6-4v4M9 17v4m6-4v4" stroke="rgba(47,124,255,1)" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <h3>Akumulatora maiņa</h3>
          <p>Ja ierīce ātri izlādējas — nomainīsim akumulatoru un pārbaudīsim barošanas ķēdes.</p>
        </article>

        <article class="card">
          <div class="iconbox" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
              <path d="M12 2v20" stroke="rgba(47,124,255,1)" stroke-width="2" stroke-linecap="round"/>
              <path d="M7 7c2-2 8-2 10 0" stroke="rgba(47,124,255,1)" stroke-width="2" stroke-linecap="round"/>
              <path d="M7 17c2 2 8 2 10 0" stroke="rgba(47,124,255,1)" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <h3>Tīrīšana pēc mitruma</h3>
          <p>Rūpīga izjaukšana, tīrīšana un atjaunošana. Jo ātrāk atnesīsiet — jo lielāka iespēja izglābt ierīci.</p>
        </article>

        <article class="card">
          <div class="iconbox" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
              <path d="M4 5h16v10H4z" stroke="rgba(47,124,255,1)" stroke-width="2"/>
              <path d="M7 19h10" stroke="rgba(47,124,255,1)" stroke-width="2" stroke-linecap="round"/>
              <path d="M9 15v4m6-4v4" stroke="rgba(47,124,255,1)" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <h3>Portatīvo datoru remonts</h3>
          <p>Barošana, matrica, klaviatūra, ligzdas, pārkaršana. Pēc remonta veicam testus slodzē.</p>
        </article>

        <article class="card">
          <div class="iconbox" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
              <path d="M12 3l8 4v6c0 5-3 8-8 9-5-1-8-4-8-9V7l8-4z" stroke="rgba(47,124,255,1)" stroke-width="2"/>
              <path d="M9 12l2 2 4-4" stroke="rgba(47,124,255,1)" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <h3>Datu atjaunošana</h3>
          <p>Palīdzam atgūt svarīgas fotogrāfijas un dokumentus. Iepriekš saskaņojam riskus un iespējas.</p>
        </article>

        <article class="card">
          <div class="iconbox" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
              <path d="M6 12h12" stroke="rgba(47,124,255,1)" stroke-width="2" stroke-linecap="round"/>
              <path d="M12 6v12" stroke="rgba(47,124,255,1)" stroke-width="2" stroke-linecap="round"/>
              <path d="M4 4h16v16H4z" stroke="rgba(47,124,255,1)" stroke-width="2"/>
            </svg>
          </div>
          <h3>Ligzdu maiņa</h3>
          <p>USB-C/Lightning, uzlāde, mikrofons, skaļrunis. Pēc maiņas pārbaudām sakarus un barošanu.</p>
        </article>

        <article class="card">
          <div class="iconbox" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
              <path d="M5 12a7 7 0 0 1 14 0" stroke="rgba(47,124,255,1)" stroke-width="2"/>
              <path d="M8 12h8" stroke="rgba(47,124,255,1)" stroke-width="2" stroke-linecap="round"/>
              <path d="M12 12v7" stroke="rgba(47,124,255,1)" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <h3>Profilaktiskā apkope</h3>
          <p>Putekļu tīrīšana, termopastas maiņa, temperatūru pārbaude. Palīdz izvairīties no pārkaršanas un “throttling”.</p>
        </article>
      </div>
    </div>
  </section>

  <!-- ===== How it works ===== -->
  <section class="section how" id="how">
    <div class="container">
      <h2 class="section-title">Kā tas darbojas</h2>
      <p class="section-lead">
        Bez “burvjiem” — caurspīdīgs process no pieteikuma līdz izsniegšanai. Jūs saprotat, ko darām un kāpēc tas maksā tieši tik.
      </p>

      <div class="steps">
        <div class="connector" aria-hidden="true">
          <span class="dotp"></span><span class="dotp"></span><span class="dotp"></span><span class="dotp"></span><span class="dotp"></span>
          <span class="dotp"></span><span class="dotp"></span><span class="dotp"></span><span class="dotp"></span><span class="dotp"></span>
          <span class="dotp"></span><span class="dotp"></span><span class="dotp"></span><span class="dotp"></span><span class="dotp"></span>
          <span class="dotp"></span><span class="dotp"></span><span class="dotp"></span><span class="dotp"></span><span class="dotp"></span>
        </div>

        <div class="step">
          <div class="step-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
              <path d="M4 6h16v12H4z"/>
              <path d="M8 18v2h8v-2"/>
              <path d="M7 9h10"/>
            </svg>
          </div>
          <h3>Pieteikums</h3>
          <p>Atstājat pieteikumu mājaslapā vai atnesat ierīci filiālē. Precizējam simptomus un pierakstām datus.</p>
        </div>

        <div class="step">
          <div class="step-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
              <path d="M12 2v3"/>
              <path d="M4.2 4.2l2.1 2.1"/>
              <path d="M2 12h3"/>
              <path d="M4.2 19.8l2.1-2.1"/>
              <path d="M12 19v3"/>
              <path d="M19.8 19.8l-2.1-2.1"/>
              <path d="M19 12h3"/>
              <path d="M19.8 4.2l-2.1 2.1"/>
              <path d="M12 7a5 5 0 1 0 0 10a5 5 0 0 0 0-10z"/>
            </svg>
          </div>
          <h3>Diagnostika</h3>
          <p>Veicam pārbaudi, izskaidrojam cēloni un saskaņojam cenu/termiņus. Bez saskaņošanas neko nedarām.</p>
        </div>

        <div class="step">
          <div class="step-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
              <path d="M13 2L3 14h7l-1 8 12-14h-7l-1-6z"/>
            </svg>
          </div>
          <h3>Remonts un izsniegšana</h3>
          <p>Salabojam, notestējam un izsniedzam rezultātu. Ja vajag — ieteikumi ierīces kopšanai.</p>
        </div>
      </div>

      <div style="height: 18px;"></div>
    </div>
  </section>

  <!-- ===== Pricing ===== -->
  <section class="section pricing" id="prices">
    <div class="container">
      <h2 class="section-title">Aptuvenās cenas</h2>
      <p class="section-lead">
        Gala cena atkarīga no modeļa un ierīces stāvokļa. Zemāk — orientieri, lai saprastu cenu līmeni.
        Precīzu cenu pateiksim pēc diagnostikas.
      </p>

      <div class="pricing-grid">
        <div class="price-card">
          <div class="price-top">
            <div>
              <p class="price-title">Diagnostika</p>
              <p class="price">15€ <small>no</small></p>
            </div>
          </div>
          <ul class="ul">
            <li>Bojājuma cēloņa noteikšana</li>
            <li>Cenas un termiņu novērtējums</li>
            <li>Ieteikumi remontam</li>
          </ul>
          <div class="price-actions">
            <a class="btn dark" href="/orders">Noformēt pieteikumu</a>
            <a class="btn" href="#contacts">Kontakti</a>
          </div>
        </div>

        <div class="price-card featured">
          <div class="price-top">
            <div>
              <p class="price-title">Ekrāna maiņa</p>
              <p class="price">45€ <small>no (darbs)</small></p>
            </div>
          </div>
          <ul class="ul">
            <li>Moduļa piemeklēšana pēc modeļa</li>
            <li>Rūpīga uzstādīšana</li>
            <li>Skāriena un attēla pārbaude</li>
          </ul>
          <div class="price-actions">
            <a class="btn dark" href="/orders">Noformēt pieteikumu</a>
            <a class="btn" href="#services">Pakalpojumi</a>
          </div>
        </div>

        <div class="price-card">
          <div class="price-top">
            <div>
              <p class="price-title">Tīrīšana / apkope</p>
              <p class="price">35€ <small>no</small></p>
            </div>
          </div>
          <ul class="ul">
            <li>Putekļu tīrīšana</li>
            <li>Termopastas maiņa (ja nepieciešams)</li>
            <li>Temperatūru tests pēc darba</li>
          </ul>
          <div class="price-actions">
            <a class="btn dark" href="/orders">Noformēt pieteikumu</a>
            <a class="btn" href="#reviews">Atsauksmes</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== Reviews ===== -->
  <section class="section reviews" id="reviews">
    <div class="container">
      <h2 class="section-title">Klientu atsauksmes</h2>
      <p class="section-lead">
        Mēs cenšamies visu izskaidrot vienkārši un neuzspiest lieko. Lūk, ko saka klienti.
      </p>

      <div class="reviews-grid">
        <div class="review">
          <div class="stars">★★★★★</div>
          <p>Ātri atrada iemeslu, visu saskaņoja par cenu, remonts aizņēma mazāk par dienu. Patika, ka visu izskaidroja.</p>
          <div class="who">
            <span>Andris</span>
            <span class="badge-round">A</span>
          </div>
        </div>
        <div class="review">
          <div class="stars">★★★★★</div>
          <p>Mainīja ekrānu — izskatās kā jauns, skāriens strādā ideāli. Vēl iedeva padomus, kā saudzēt ierīci.</p>
          <div class="who">
            <span>Marija</span>
            <span class="badge-round">M</span>
          </div>
        </div>
        <div class="review">
          <div class="stars">★★★★☆</div>
          <p>Nedaudz pagaidīju rindā, bet rezultāts lielisks. Nebija nekādu “pārsteigumu” par izmaksām.</p>
          <div class="who">
            <span>Deniss</span>
            <span class="badge-round">D</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== FAQ ===== -->
  <section class="section faq" id="faq">
    <div class="container">
      <h2 class="section-title">FAQ</h2>
      <p class="section-lead">
        Īsi atbildam uz to, ko visbiežāk jautā pirms remonta.
      </p>

      <div class="faq-list">
        <div class="faq-item">
          <button class="faq-question" type="button">
            Cik maksā diagnostika?
            <span class="faq-arrow">+</span>
          </button>
          <div class="faq-answer">
            <div class="faq-answer-inner">
              Diagnostika ir maksas pakalpojums, bet tās izmaksas tiek atskaitītas no remonta cenas, ja izvēlaties remontu pie mums.
            </div>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-question" type="button">
            Cik ilgs ir remonts?
            <span class="faq-arrow">+</span>
          </button>
          <div class="faq-answer">
            <div class="faq-answer-inner">
              Neliels remonts — no 30 minūtēm. Sarežģītāki gadījumi — no 1 līdz 3 dienām. Precīzu termiņu pasakām pēc diagnostikas.
            </div>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-question" type="button">
            Vai ir garantija remontam?
            <span class="faq-arrow">+</span>
          </button>
          <div class="faq-answer">
            <div class="faq-answer-inner">
              Jā. Sniedzam garantiju veiktajiem darbiem un nomainītajām detaļām. Termiņš atkarīgs no remonta veida.
            </div>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-question" type="button">
            Ko darīt, ja ierīci nevar salabot?
            <span class="faq-arrow">+</span>
          </button>
          <div class="faq-answer">
            <div class="faq-answer-inner">
              Šādā gadījumā apmaksājat tikai diagnostiku. Bez slēptām izmaksām vai uzspiestiem pakalpojumiem.
            </div>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-question" type="button">
            Vai izmantojat oriģinālas detaļas?
            <span class="faq-arrow">+</span>
          </button>
          <div class="faq-answer">
            <div class="faq-answer-inner">
              Cik iespējams — oriģinālas. Ja ir alternatīvas, iepriekš izskaidrojam atšķirības un ļaujam izvēlēties.
            </div>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-question" type="button">
            Ko darīt, ja ierīce ir samirkusi?
            <span class="faq-arrow">+</span>
          </button>
          <div class="faq-answer">
            <div class="faq-answer-inner">
              Izslēdziet ierīci un nemēģiniet to lādēt. Jo ātrāk atnesīsiet uz servisu, jo lielāka iespēja atjaunot.
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== Contacts ===== -->
  <section class="section contacts" id="contacts">
    <div class="container">
      <h2 class="section-title">Kontakti un filiāles</h2>
      <p class="section-lead">
        Varat atnest ierīci tuvākajā filiālē vai uzrakstīt mums. Ieteiksim, ko vislabāk darīt jūsu situācijā.
      </p>

      <div class="contacts-grid">
        <div class="contact-card">
          <b style="font-size:16px; letter-spacing:-.01em;">Filiāles</b>
          <div class="branches">
            <div class="branch">
              <b>DeviceLab — Centrs</b>
              <span>Adrese: Rīga, Centrs</span>
              <span>Darba laiks: P–Pk 09:00–18:00</span>
              <span>Tālrunis: +371 2000 1234</span>
            </div>
            <div class="branch">
              <b>DeviceLab — Purvciems</b>
              <span>Adrese: Rīga, Purvciems</span>
              <span>Darba laiks: P–S 10:00–19:00</span>
              <span>Tālrunis: +371 2000 5678</span>
            </div>
          </div>
          <!-- Svarīgi: šī ir demo lapa. Reālajā vietnē var pieslēgt čatu un pieraksta sistēmu. -->
        </div>

        <div class="contact-card" id="order">
          <b style="font-size:16px; letter-spacing:-.01em;">Uzraksti mums</b>
          <div style="margin-top:12px; display:grid; gap:10px;">
            <input class="input" id="cname" type="text" placeholder="Jūsu vārds" />
            <input class="input" id="ccontact" type="text" placeholder="Tālrunis vai e-pasts" />
            <textarea class="textarea" id="cmsg" placeholder="Ziņa"></textarea>
            <button class="btn dark" id="sendMsg" type="button">Nosūtīt</button>
            <div class="ok" id="msgOk" style="display:none; font-size:12px; font-weight:800; color:#157a2b; background:rgba(21,122,43,.08); border:1px solid rgba(21,122,43,.18); padding:10px 12px; border-radius:12px;">
              Ziņa nosūtīta. Mēs ar jums sazināsimies.
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== Footer ===== -->
  <footer class="footer">
    <div class="container">
      <div class="footer-row">
        <div style="font-weight:800;">© <span id="year"></span> DeviceLab</div>
        <div style="display:flex; gap:14px; flex-wrap:wrap;">
          <a href="#services">Pakalpojumi</a>
          <a href="#prices">Cenas</a>
          <a href="#contacts">Kontakti</a>
          <a href="#faq">FAQ</a>
          <a href="#top">Uz augšu</a>
        </div>
      </div>
    </div>
  </footer>
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue'

const root = ref(null)

const cleanups = []

onMounted(() => {
  const el = root.value
  if (!el) return

  // Sticky header background
  const header = el.querySelector('#header')
  const onScroll = () => {
    if (!header) return
    header.classList.toggle('is-scrolled', window.scrollY > 12)
  }
  window.addEventListener('scroll', onScroll, { passive: true })
  cleanups.push(() => window.removeEventListener('scroll', onScroll))
  onScroll()

  // Mobile menu (sheet)
  const burger = el.querySelector('#burger')
  const sheet = el.querySelector('#sheet')
  const closeBtn = el.querySelector('#closeSheet')
  const overlay = el.querySelector('.sheet-overlay')

  const openSheet = () => sheet?.classList.add('open')
  const closeSheet = () => sheet?.classList.remove('open')

  burger?.addEventListener('click', openSheet)
  closeBtn?.addEventListener('click', closeSheet)
  overlay?.addEventListener('click', closeSheet)

  if (burger) cleanups.push(() => burger.removeEventListener('click', openSheet))
  if (closeBtn) cleanups.push(() => closeBtn.removeEventListener('click', closeSheet))
  if (overlay) cleanups.push(() => overlay.removeEventListener('click', closeSheet))

  const onKeyDown = (e) => {
    if (e.key === 'Escape') closeSheet()
  }
  window.addEventListener('keydown', onKeyDown)
  cleanups.push(() => window.removeEventListener('keydown', onKeyDown))

  // Smooth anchors with fixed header offset
  const offset = 84
  el.querySelectorAll('a[href^="#"]').forEach(a => {
    const handler = (e) => {
      const id = a.getAttribute('href')
      if (!id || id === '#') return
      const target = el.querySelector(id)
      if (!target) return
      e.preventDefault()
      closeSheet()

      const top = target.getBoundingClientRect().top + window.scrollY - offset
      window.scrollTo({ top, behavior: 'smooth' })
    }
    a.addEventListener('click', handler)
    cleanups.push(() => a.removeEventListener('click', handler))
  })

  // Contact form demo message
  const sendMsg = el.querySelector('#sendMsg')
  const msgOk = el.querySelector('#msgOk')
  const onSend = () => {
    if (!msgOk) return
    msgOk.style.display = 'block'
    window.setTimeout(() => (msgOk.style.display = 'none'), 2600)
  }
  sendMsg?.addEventListener('click', onSend)
  if (sendMsg) cleanups.push(() => sendMsg.removeEventListener('click', onSend))

  // Footer year
  const yearEl = el.querySelector('#year')
  if (yearEl) yearEl.textContent = String(new Date().getFullYear())

  // FAQ accordion
  el.querySelectorAll('.faq-question').forEach((btn) => {
    const handler = () => {
      const item = btn.closest('.faq-item')
      const ans = item?.querySelector('.faq-answer')
      const open = item.classList.toggle('active')
      if (!ans) return
      ans.style.maxHeight = open ? ans.scrollHeight + 'px' : '0px'
    }
    btn.addEventListener('click', handler)
    cleanups.push(() => btn.removeEventListener('click', handler))
  })
})

onBeforeUnmount(() => {
  cleanups.forEach(fn => {
    try { fn() } catch { /* ignore */ }
  })
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

/* Safe global reset */
:global(body){ margin: 0; }

.landing{
      
      min-height: 100vh;--bg1:#0a2a7a;
      --bg2:#0b3c96;
      --bg3:#0a2a7a;

      --text:#ffffff;
      --muted:rgba(255,255,255,.74);

      --ink:#1c2430;
      --ink2:#2a3444;
      --gray:#6b7686;

      --card:#ffffff;

      --accent:#2f7cff;
      --accent2:#4aa3ff;

      --shadow: 0 30px 80px rgba(0,0,0,.25);
      --softShadow: 0 18px 45px rgba(0,0,0,.18);

      --container: 1160px;
      --radius: 18px;
    }

    *{box-sizing:border-box}

    html{ scroll-behavior: smooth; } /* + */
.landing{
      margin:0;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      color:var(--text);
      background:#0b2c7f;
      overflow-x:hidden;
    }

    a{color:inherit; text-decoration:none}
    .container{
      width:min(var(--container), calc(100% - 40px));
      margin:0 auto;
    }

    /* ===== Header ===== */
    .header{
      position:fixed;
      left:0; right:0; top:0;
      z-index:50;
      padding:18px 0;
      transition: background .25s ease, backdrop-filter .25s ease, border-color .25s ease;
      border-bottom: 1px solid rgba(255,255,255,0);
    }
    .header.is-scrolled{
      background: rgba(7, 24, 70, .55);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .header-row{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:18px;
    }
    .brand{
      display:flex; align-items:center; gap:10px;
      font-weight:800;
      letter-spacing:.2px;
    }
    .brand svg{width:34px;height:34px; flex:0 0 auto}
    .nav{
      display:flex;
      align-items:center;
      gap:18px;
      font-weight:600;
      color: rgba(255,255,255,.86);
      flex-wrap:wrap;
      justify-content:center;
    }
    .nav a{opacity:.92; padding:8px 10px; border-radius:10px;}
    .nav a:hover{opacity:1; background: rgba(255,255,255,.07);}
    .cta{
      display:flex; align-items:center; gap:12px;
    }
    .btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      padding:12px 18px;
      border-radius: 12px;
      border: 1px solid rgba(255,255,255,.18);
      background: rgba(255,255,255,.08);
      color:#fff;
      font-weight:700;
      transition: transform .15s ease, background .15s ease, border-color .15s ease;
      cursor:pointer;
      user-select:none;
      white-space:nowrap;
    }
    .btn:hover{
      transform: translateY(-1px);
      background: rgba(255,255,255,.12);
      border-color: rgba(255,255,255,.26);
    }
    .btn.primary{
      background: linear-gradient(180deg, var(--accent2), var(--accent));
      border-color: rgba(0,0,0,0);
      box-shadow: 0 18px 35px rgba(47,124,255,.35);
    }
    .btn.primary:hover{transform: translateY(-1px) scale(1.01)}
    .btn.light{
      background:#fff;
      color:#10336e;
      border-color: rgba(255,255,255,.0);
      box-shadow: 0 16px 40px rgba(0,0,0,.18);
    }
    .btn.light:hover{transform: translateY(-1px)}
    .btn.dark{
      background: #0a66ff;
      border-color: rgba(0,0,0,0);
      box-shadow: 0 16px 36px rgba(10,102,255,.25);
    }
    .btn.dark:hover{ transform: translateY(-1px); }

    .burger{display:none}
    @media (max-width: 980px){
      .nav{display:none}
      .burger{display:inline-flex}
    }

    /* mobile sheet */
    .sheet{
      position:fixed;
      inset:0;
      background: rgba(0,0,0,.35);
      z-index:60;
      display:none;
    }
    .sheet.open{display:block;}
    .sheet-panel{
      position:absolute;
      right:14px; top:14px;
      width:min(420px, calc(100% - 28px));
      border-radius: 16px;
      background: rgba(7, 24, 70, .72);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255,255,255,.12);
      box-shadow: 0 30px 80px rgba(0,0,0,.35);
      overflow:hidden;
    }
    .sheet-head{
      display:flex; align-items:center; justify-content:space-between;
      padding: 14px 14px 10px;
      border-bottom: 1px solid rgba(255,255,255,.10);
    }
    .sheet-head b{font-size:14px; letter-spacing:.02em}
    .sheet-links{padding: 10px;}
    .sheet-links a{
      display:block;
      padding: 12px 12px;
      border-radius: 12px;
      color: rgba(255,255,255,.92);
      font-weight:700;
    }
    .sheet-links a:hover{background: rgba(255,255,255,.08);}

    /* ===== Hero ===== */
    .hero{
      position:relative;
      padding: 110px 0 0;
      background: radial-gradient(1200px 800px at 20% 10%, rgba(74,163,255,.18), transparent 60%),
                  radial-gradient(900px 600px at 80% 30%, rgba(47,124,255,.18), transparent 55%),
                  linear-gradient(135deg, var(--bg1), var(--bg2) 55%, var(--bg3));
      overflow:hidden;
      min-height: 1020px;
    }
    .hero-lines{
      position:absolute;
      inset:0;
      opacity:.35;
      pointer-events:none;
      mix-blend-mode: screen;
    }
    .hero-grid{
      display:grid;
      grid-template-columns: 1.05fr .95fr;
      align-items:center;
      gap:40px;
      padding: 60px 0 80px;
    }
    .kicker{
      font-size:13px;
      letter-spacing: .18em;
      text-transform: uppercase;
      color: rgba(255,255,255,.78);
      font-weight:700;
    }
    h1{
      margin: 14px 0 18px;
      font-size: clamp(34px, 4vw, 56px);
      line-height: 1.05;
      letter-spacing: -0.02em;
    }
    .lead{
      max-width: 58ch;
      margin: 0 0 28px;
      color: var(--muted);
      line-height:1.7;
      font-size: 16px;
    }
    .hero-actions{
      display:flex;
      gap:14px;
      flex-wrap:wrap;
      align-items:center;
    }
    .hero-badges{
      display:flex;
      align-items:center;
      gap:12px;
      margin-top: 22px;
      color: rgba(255,255,255,.78);
      font-weight:700;
      font-size:14px;
      flex-wrap:wrap;
    }
    .pill{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 10px 12px;
      border-radius: 999px;
      border: 1px solid rgba(255,255,255,.16);
      background: rgba(0,0,0,.14);
    }
    .pill svg{width:16px;height:16px;opacity:.95}

    /* ===== Mockup / demo card ===== */
    .mockwrap{
      position:relative;
      display:flex;
      justify-content:flex-end;
    }

    @keyframes floatY {
      0%, 100% { transform: translate3d(0, calc(var(--amp, 12px) * -1), 0); }
      50%      { transform: translate3d(0, var(--amp, 12px), 0); }
    }

    .mock{
      width: min(540px, 100%);
      border-radius: 34px;
      background: rgba(255,255,255,.92);
      box-shadow: var(--shadow);
      position:relative;
      overflow:hidden;
      animation: floatY 8.2s ease-in-out infinite;
      --amp: 10px;
    }
    .mock::before{
      content:"";
      position:absolute;
      inset:0;
      background:
        radial-gradient(540px 260px at 55% 35%, rgba(47,124,255,.20), transparent 60%),
        linear-gradient(180deg, rgba(255,255,255,1), rgba(245,247,252,1));
    }
    .mock-inner{
      position:relative;
      margin: 18px;
      border-radius: 24px;
      background: #f2f4f8;
      overflow:hidden;
      border: 1px solid rgba(16,51,110,.08);
    }
    .mock-topbar{
      height:44px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding: 0 14px;
      background: rgba(255,255,255,.78);
      border-bottom: 1px solid rgba(16,51,110,.07);
      color:#224;
      font-size:12px;
    }
    .dots{display:flex; gap:7px}
    .dot{width:10px;height:10px;border-radius:99px;background: rgba(16,51,110,.18);}

    .demo-card{
      padding: 18px;
      display:grid;
      gap: 12px;
    }
    .demo-title{
      color:#1b2a44;
      font-weight:800;
      font-size: 18px;
      line-height:1.2;
      margin-bottom:2px;
    }
    .demo-sub{
      color:#51607a;
      font-size:12px;
      line-height:1.6;
      margin-bottom:6px;
    }
    .grid2{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }
    .field{
      display:flex;
      flex-direction:column;
      gap: 6px;
    }
    .field label{
      font-size: 12px;
      font-weight:800;
      color: rgba(27,42,68,.75);
    }
    .input, .select, .textarea{
      width:100%;
      padding: 12px 12px;
      border-radius: 12px;
      border: 1px solid rgba(16,51,110,.12);
      background: rgba(255,255,255,.86);
      color:#1b2a44;
      outline:none;
      font: inherit;
      font-size: 13px;
    }
    .textarea{ min-height: 88px; resize: vertical; }

    /* demo-only: looks filled, but not editable */
    .demo-only .input,
    .demo-only .select,
    .demo-only .textarea{
      background: rgba(255,255,255,.74);
      color: rgba(27,42,68,.92);
      border-color: rgba(16,51,110,.10);
    }
    .demo-only .input:disabled,
    .demo-only .select:disabled,
    .demo-only .textarea:disabled{
      opacity: 1;
      -webkit-text-fill-color: rgba(27,42,68,.92);
    }

    .demo-actions{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 12px;
      flex-wrap:wrap;
      margin-top: 6px;
    }
    .hint{
      font-size: 12px;
      color: rgba(27,42,68,.62);
      line-height:1.4;
    }

    /* ===== Floating decorations ===== */
    .float{
      position:absolute;
      filter: drop-shadow(0 25px 50px rgba(0,0,0,.28));
      opacity:.92;
      pointer-events:none;
      overflow: visible;
      will-change: transform;
      animation: floatY var(--dur, 7s) ease-in-out infinite;
    }
    .float.one{ left: 5%; top: 70px; width: 220px; --dur: 7.6s; --amp: 14px;}
    .float.two{ right: 5%; top: 120px; width: 140px; opacity:.85; --dur: 6.2s; --amp: 10px;}
    .float.three{ right: -20px; bottom: -10px; width: 420px; opacity:.9; --dur: 8.8s; --amp: 16px;}

    @media (prefers-reduced-motion: reduce){
      .float, .mock{ animation: none !important; }
    }

    /* wave divider */
    .wave{
      position:absolute;
      left:0; right:0; bottom:-1px;
      height:120px;
      pointer-events:none;
      display:block;
      width: 100vw;
      left: 50%;
      right: auto;
      transform: translateX(-50%);
    }

    @media (max-width: 980px){
      .hero-grid{ grid-template-columns: 1fr; padding-bottom: 120px; }
      .mockwrap{justify-content:flex-start}
      .float.one{ left:-30px; top: 40px; width: 190px; opacity:.6}
      .float.two{ right:-20px; top: 70px; width: 120px; opacity:.6}
      .float.three{ right:-90px; bottom:-60px; width: 360px; opacity:.65}
      .grid2{ grid-template-columns: 1fr; }
    }

    /* ===== Section base ===== */
    .section{
      padding: 84px 0;
    }
    .section-title{
      margin:0 0 12px;
      font-size: clamp(28px, 3.2vw, 44px);
      letter-spacing:-.02em;
      line-height:1.12;
      color: var(--ink);
    }
    .section-lead{
      margin:0 0 28px;
      color: rgba(28,36,48,.62);
      line-height:1.85;
      font-size: 15px;
      max-width: 70ch;
    }

    /* ===== Services ===== */
    .services{
      background: #f6f8fc;
      color: var(--ink);
    }
    .services-grid{
      display:grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 22px;
    }
    .card{
      background: var(--card);
      border-radius: 16px;
      padding: 26px 22px;
      border: 1px solid rgba(28,36,48,.06);
      box-shadow: 0 10px 30px rgba(12,25,48,.06);
      transition: transform .18s ease, box-shadow .18s ease;
      min-height: 190px;
    }
    .card:hover{
      transform: translateY(-4px);
      box-shadow: 0 18px 45px rgba(12,25,48,.10);
    }
    .iconbox{
      width:46px;height:46px;border-radius:14px;
      display:grid;place-items:center;
      background: rgba(47,124,255,.10);
      border: 1px solid rgba(47,124,255,.14);
      margin-bottom:14px;
    }
    .card h3{ margin: 0 0 8px; font-size: 18px; letter-spacing: -.01em; }
    .card p{ margin:0; color: var(--gray); line-height:1.65; font-size: 14px; }

    @media (max-width: 980px){
      .services-grid{grid-template-columns: repeat(2, 1fr)}
    }
    @media (max-width: 560px){
      .services-grid{grid-template-columns: 1fr}
    }

    /* ===== How it works (Steps) ===== */
    .how{
      background:#fff;
      color: var(--ink);
    }
    .steps{
      margin-top: 34px;
      position:relative;
      display:grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 36px;
      align-items:start;
      text-align:center;
    }
    .connector{
      position:absolute;
      left: calc(16.6% + 92px);
      right: calc(16.6% + 92px);
      top: 54px;
      height: 16px;
      pointer-events:none;
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap: 22px;
      opacity:.95;
    }
    .dotp{
      width: 6px;
      height: 6px;
      border-radius: 999px;
      background: rgba(10,102,255,.35);
      transform: scale(1);
      animation: dotPulse 1.6s ease-in-out infinite;
    }
    .dotp:nth-child(1)  { animation-delay: 0s; }
    .dotp:nth-child(2)  { animation-delay: .08s; }
    .dotp:nth-child(3)  { animation-delay: .16s; }
    .dotp:nth-child(4)  { animation-delay: .24s; }
    .dotp:nth-child(5)  { animation-delay: .32s; }
    .dotp:nth-child(6)  { animation-delay: .40s; }
    .dotp:nth-child(7)  { animation-delay: .48s; }
    .dotp:nth-child(8)  { animation-delay: .56s; }
    .dotp:nth-child(9)  { animation-delay: .64s; }
    .dotp:nth-child(10) { animation-delay: .72s; }
    .dotp:nth-child(11) { animation-delay: .80s; }
    .dotp:nth-child(12) { animation-delay: .88s; }
    .dotp:nth-child(13) { animation-delay: .96s; }
    .dotp:nth-child(14) { animation-delay: 1.04s; }
    .dotp:nth-child(15) { animation-delay: 1.12s; }
    .dotp:nth-child(16) { animation-delay: 1.20s; }
    .dotp:nth-child(17) { animation-delay: 1.28s; }
    .dotp:nth-child(18) { animation-delay: 1.36s; }
    .dotp:nth-child(19) { animation-delay: 1.44s; }
    .dotp:nth-child(20) { animation-delay: 1.52s; }

    @keyframes dotPulse{
      0%, 100% { transform: scale(.85); opacity: .55; }
      50%      { transform: scale(1.9); opacity: 1; }
    }

    .step{
      padding: 0 10px;
    }
    .step-icon{
      width: 108px; height: 108px;
      margin: 0 auto 22px;
      border-radius: 28px;
      background: #fff;
      box-shadow: 0 26px 60px rgba(0,0,0,.08);
      display:grid;
      place-items:center;
      position:relative;
      transition: background .18s ease, box-shadow .18s ease, transform .18s ease;
    }
    .step-icon svg{
      width: 42px; height: 42px;
      stroke: #0a66ff;
      stroke-width: 2.2;
      fill: none;
      transition: stroke .18s ease;
    }
    .step:hover .step-icon{
      background:#0a66ff;
      box-shadow: 0 28px 70px rgba(10,102,255,.25);
      transform: translateY(-2px);
    }
    .step:hover .step-icon svg{ stroke:#fff; }
    .step:hover h3{ color:#0a66ff; }

    .step h3{
      margin:0 0 10px;
      font-size: 22px;
      letter-spacing:-.01em;
      transition: color .18s ease;
    }
    .step p{
      margin:0 auto;
      max-width: 40ch;
      color: rgba(28,36,48,.55);
      line-height:1.7;
      font-size: 14px;
    }

    @media (max-width: 980px){
      .steps{ grid-template-columns: 1fr; gap: 44px; }
      .connector{ display:none; }
    }
    @media (prefers-reduced-motion: reduce){
      .dotp{ animation: none !important; }
      .step:hover .step-icon{ transform:none; }
    }

    /* ===== Pricing ===== */
    .pricing{
      background: linear-gradient(180deg, #f7fbff, #ffffff);
      color: var(--ink);
    }
    .pricing-grid{
      display:grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 22px;
      margin-top: 22px;
    }
    .price-card{
      border-radius: 18px;
      background:#fff;
      border: 1px solid rgba(28,36,48,.08);
      box-shadow: 0 18px 55px rgba(12,25,48,.08);
      padding: 24px;
      display:flex;
      flex-direction:column;
      gap: 14px;
      min-height: 310px;
    }
    .price-card.featured{
      border-color: rgba(10,102,255,.22);
      box-shadow: 0 22px 70px rgba(10,102,255,.12);
    }
    .price-top{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 12px;
    }
    .price-title{
      font-weight:900;
      font-size: 18px;
      letter-spacing:-.01em;
      margin:0;
    }
    .price{
      font-size: 36px;
      font-weight:900;
      letter-spacing:-.02em;
      margin: 0;
    }
    .price small{
      font-size: 14px;
      font-weight:800;
      color: rgba(28,36,48,.55);
    }
    .ul{
      margin:0;
      padding-left: 18px;
      color: rgba(28,36,48,.62);
      line-height:1.8;
      font-size: 14px;
    }
    .ul li{ margin: 6px 0; }
    .price-actions{
      margin-top:auto;
      display:flex;
      gap: 10px;
      flex-wrap:wrap;
    }

    @media (max-width: 980px){
      .pricing-grid{ grid-template-columns: 1fr; }
    }

    /* ===== Reviews ===== */
    .reviews{
      background:#fff;
      color: var(--ink);
    }
    .reviews-grid{
      display:grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 22px;
      margin-top: 22px;
    }
    .review{
      background:#fff;
      border: 1px solid rgba(28,36,48,.08);
      border-radius: 18px;
      padding: 22px;
      box-shadow: 0 18px 55px rgba(12,25,48,.08);
      display:flex;
      flex-direction:column;
      gap: 12px;
      min-height: 220px;
    }
    .stars{ color:#0a66ff; font-weight:900; letter-spacing:.06em; }
    .review p{ margin:0; color: rgba(28,36,48,.62); line-height:1.75; font-size: 14px; }
    .who{
      margin-top:auto;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
      color: rgba(28,36,48,.72);
      font-weight:800;
      font-size: 13px;
    }
    .badge-round{
      width:40px;height:40px;border-radius:999px;
      background: rgba(10,102,255,.10);
      border: 1px solid rgba(10,102,255,.16);
      display:grid;place-items:center;
      color:#0a66ff;
      font-weight:900;
    }
    @media (max-width: 980px){
      .reviews-grid{ grid-template-columns: 1fr; }
    }

    /* ===== Contacts ===== */
    .contacts{
      background: #f6f1ea;
      color: var(--ink);
      position:relative;
      overflow:hidden;
    }
    .contacts-grid{
      display:grid;
      grid-template-columns: 1.1fr .9fr;
      gap: 26px;
      align-items:start;
      margin-top: 18px;
    }
    .contact-card{
      background:#fff;
      border-radius: 18px;
      border: 1px solid rgba(28,36,48,.08);
      box-shadow: 0 18px 55px rgba(12,25,48,.08);
      padding: 22px;
    }
    .branches{
      display:grid;
      grid-template-columns: 1fr;
      gap: 12px;
      margin-top: 10px;
    }
    .branch{
      border-radius: 14px;
      border: 1px solid rgba(28,36,48,.08);
      padding: 14px;
      background: linear-gradient(135deg, rgba(255,255,255,.98), rgba(245,248,255,.98));
    }
    .branch b{display:block; margin-bottom:6px;}
    .branch span{display:block; color: rgba(28,36,48,.62); line-height:1.6; font-size: 14px;}
    .small{
      font-size:12px;
      color: rgba(28,36,48,.62);
      line-height:1.6;
      margin: 10px 0 0;
    }

    @media (max-width: 980px){
      .contacts-grid{ grid-template-columns: 1fr; }
    }

    /* ===== Footer ===== */
    .footer{
      background: #071846;
      color: rgba(255,255,255,.86);
      padding: 28px 0;
      border-top: 1px solid rgba(255,255,255,.08);
    }
    .footer-row{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 12px;
      flex-wrap:wrap;
    }
    .footer a{opacity:.92}
    .footer a:hover{opacity:1}

    .sr{position:absolute;left:-9999px}

    /* ===== FAQ ===== */
    .faq{
      background:#fff;
      color: var(--ink);
    }
    .faq-list{
      margin-top: 24px;
      display:grid;
      gap: 14px;
    }
    .faq-item{
      border: 1px solid rgba(28,36,48,.08);
      border-radius: 18px;
      overflow:hidden;
      background:#fff;
      box-shadow: 0 18px 55px rgba(12,25,48,.06);
    }
    .faq-question{
      width:100%;
      padding: 18px 20px;
      background: none;
      border: none;
      cursor: pointer;
      text-align:left;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 14px;
      font: inherit;
      font-weight: 800;
      color: rgba(28,36,48,.92);
    }
    .faq-question:hover{
      background: rgba(10,102,255,.04);
    }
    .faq-arrow{
      width: 34px;
      height: 34px;
      border-radius: 12px;
      display:grid;
      place-items:center;
      background: rgba(10,102,255,.08);
      border: 1px solid rgba(10,102,255,.14);
      color: #0a66ff;
      font-weight: 900;
      flex: 0 0 auto;
      transition: transform .28s ease;
    }
    .faq-answer{
      max-height: 0;
      opacity: 0;
      overflow:hidden;
      transition: max-height .38s ease, opacity .22s ease;
    }
    .faq-answer-inner{
      padding: 0 20px 18px;
      color: rgba(28,36,48,.62);
      line-height: 1.75;
      font-size: 14px;
    }
    .faq-item.active .faq-answer{
      opacity: 1;
    }
    .faq-item.active .faq-arrow{
      transform: rotate(45deg);
    }
</style>
