<?php include './include/header.php'; ?>

<main>
  <div style="max-width:1100px; margin:0 auto; padding-bottom: 80px;">

    <!-- HERO SLIDER -->
    <section class="hero">
      <div class="hero-inner">
        <div class="slides">
        
          <div class="slide active">
            <img src="/dokju/images/slider_best.png" alt="베스트셀러">
            <div class="slide-text">
              <h2>BEST SELLER</h2>
              <p>지금 바로 직접 확인하세요<br>가장 사랑받는 프리미엄 사케</p>
              <a href="/dokju/shop.php?sort=popular" class="btn">자세히 보기</a>
            </div>
          </div>
      
          <div class="slide">
            <img src="/dokju/images/slider_event.jpg" alt="사케 추천">
            <div class="slide-text">
              <h2>FIND YOUR SAKE</h2>
              <p>나에게 딱 맞는 술은 무엇일까?<br>1분 만에 취향 찾기</p>
              <a href="/dokju/sake_test.php" class="btn">테스트 시작하기</a>
            </div>
          </div>
      
          <div class="slide">
            <img src="/dokju/images/slider_new.jpg" alt="신상품">
            <div class="slide-text">
              <h2>NEW ARRIVAL</h2>
              <p>신규 입고 사케 컬렉션<br>양조장의 철학을 담다</p>
              <a href="/dokju/shop.php" class="btn">상품 보기</a>
            </div>
          </div>
          
        </div>
        
        <!-- Controls (Dots) -->
        <div class="slider-dots"></div>
      </div>
    </section>

    <!-- INTRO -->
    <section class="dark fade-in-section">
      <div class="flex">
        <div class="img-wrap">
          <img class="ink" src="/dokju/images/dokuri.jpg" alt="도쿠리와 잔">
        </div>
        <div class="text-wrap">
          <h2 class="section-title">일본 술 소개</h2>
          <div class="section-content">
            <h3 style="font-size:20px; color:#a89f91; margin-bottom:16px; letter-spacing:2px;">전통의 미학</h3>
            <p style="margin-bottom:32px;">
              사케는 쌀과 물, 누룩을 이용해 빚은 일본의 전통주입니다.<br>
              단순한 재료에서 시작되지만, 쌀의 정미율과 양조장의 제법,
              그리고 숙성 방식에 따라 수천 가지의 표정을 지니게 됩니다.
            </p>
            
            <ul style="list-style:none; padding:0; border-left:2px solid #a89f91; padding-left:20px;">
              <li style="margin-bottom:12px;"><strong style="color:#efe8db;">원료</strong> : 엄선된 쌀과 맑은 물</li>
              <li style="margin-bottom:12px;"><strong style="color:#efe8db;">방식</strong> : 전통 누룩 발효</li>
              <li><strong style="color:#efe8db;">풍미</strong> : 섬세한 향과 깊은 맛</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- TYPES (SUMMARY ONLY) -->
    <section class="dark fade-in-section" style="background:#262421;"> <!-- Slightly darker for contrast -->
      <div class="flex reverse">
        <div class="text-wrap">
          <h2 class="section-title">일본 술의 분류</h2>
          <div class="section-content" style="margin-bottom:32px;">
            <p style="margin-bottom:24px;">
              제법과 정미율에 따라 각기 다른 개성을 지닙니다.<br>
              취향에 맞는 사케를 찾아보세요.
            </p>
            <ul style="list-style:none; padding:0; border-top:1px solid rgba(255,255,255,0.15);">
              <li style="border-bottom:1px solid rgba(255,255,255,0.15); padding:14px 0; display:flex; justify-content:space-between; align-items:center;">
                <span>준마이슈</span> <span style="font-size:14px; opacity:0.6;">Rice Only</span>
              </li>
              <li style="border-bottom:1px solid rgba(255,255,255,0.15); padding:14px 0; display:flex; justify-content:space-between; align-items:center;">
                <span>혼조조</span> <span style="font-size:14px; opacity:0.6;">Crisp & Dry</span>
              </li>
              <li style="border-bottom:1px solid rgba(255,255,255,0.15); padding:14px 0; display:flex; justify-content:space-between; align-items:center;">
                <span>긴조 / 다이긴조</span> <span style="font-size:14px; opacity:0.6;">Premium Aromatic</span>
              </li>
            </ul>
          </div>
        </div>
        <div class="img-wrap">
          <img class="ink" src="/dokju/images/sake_bottle.jpg" alt="사케 병">
        </div>
      </div>
    </section>

  </div>
</main>

<script>
const slides = document.querySelectorAll('.slide');
const dotsContainer = document.querySelector('.slider-dots');
let current = 0;
let timer;

// Initialize Dots
if(slides.length > 0 && dotsContainer) {
    slides.forEach((_, i) => {
        const dot = document.createElement('span');
        dot.classList.add('dot');
        if (i === 0) dot.classList.add('active');
        dot.addEventListener('click', () => {
            showSlide(i);
            resetTimer();
        });
        dotsContainer.appendChild(dot);
    });
}

const dots = document.querySelectorAll('.dot');

function showSlide(index) {
  slides.forEach(slide => slide.classList.remove('active'));
  dots.forEach(dot => dot.classList.remove('active'));
  
  if(slides[index]) slides[index].classList.add('active');
  if(dots[index]) dots[index].classList.add('active');
  current = index;
}

function nextSlide() {
  showSlide((current + 1) % slides.length);
}

function prevSlide() {
  showSlide((current - 1 + slides.length) % slides.length);
}

// Swipe & Mouse Drag Support
let touchStartX = 0;
let touchEndX = 0;
let isDragging = false;
let startMouseX = 0;

const sliderContainer = document.querySelector('.hero'); 

if(sliderContainer) {
    // Touch Events
    sliderContainer.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
    }, {passive: true});

    sliderContainer.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, {passive: true});
    
    // Mouse Events for PC
    sliderContainer.style.cursor = 'grab'; // Default cursor
    
    sliderContainer.addEventListener('mousedown', e => {
        isDragging = true;
        startMouseX = e.clientX;
        sliderContainer.style.cursor = 'grabbing';
        e.preventDefault(); // Prevent text selection
    });
    
    sliderContainer.addEventListener('mouseup', e => {
        if(!isDragging) return;
        isDragging = false;
        sliderContainer.style.cursor = 'grab';
        
        const endMouseX = e.clientX;
        // Logic same as handleSwipe
        if (endMouseX < startMouseX - 50) {
            nextSlide();
            resetTimer();
        } else if (endMouseX > startMouseX + 50) {
            prevSlide();
            resetTimer();
        }
    });
    
    sliderContainer.addEventListener('mouseleave', () => {
        isDragging = false;
        sliderContainer.style.cursor = 'grab';
    });
}

function handleSwipe() {
    if (touchEndX < touchStartX - 50) {
        nextSlide();
        resetTimer();
    }
    if (touchEndX > touchStartX + 50) {
        prevSlide();
        resetTimer();
    }
}

function startAuto() {
  timer = setInterval(nextSlide, 5000);
}

function resetTimer() {
  clearInterval(timer);
  startAuto();
}

startAuto();

// Scroll Animation
const observerOptions = {
  root: null,
  rootMargin: '0px',
  threshold: 0.1
};

const observer = new IntersectionObserver((entries, observer) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('is-visible');
      observer.unobserve(entry.target);
    }
  });
}, observerOptions);

document.querySelectorAll('.fade-in-section').forEach(section => {
  observer.observe(section);
});
</script>

<?php include './include/footer.php'; ?>
