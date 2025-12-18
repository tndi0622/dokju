<?php include './include/header.php'; ?>
<link rel="stylesheet" href="/dokju/css/intro.css?v=<?php echo time(); ?>">

<section class="intro-section">
  <div class="intro-inner">

    <!-- MAIN TABS -->
    <ul class="tabs" id="typeTabs">
      <li class="active" data-type="junmai">준마이슈</li>
      <li data-type="honjozo">혼조조</li>
      <li data-type="ginjo">긴조</li>
      <li data-type="daiginjo">다이긴조</li>
      <li data-type="futsu">후츠슈</li>
    </ul>

    <!-- CONTENT -->
    <div class="intro-content">
      <div class="intro-image">
        <img src="" id="sakeImage" alt="사케 이미지">
      </div>
      <div class="intro-text" id="sakeText"></div>
    </div>

  </div>
</section>

<script>
const sakeData = {
  junmai: {
    title: "純米酒 | 준마이슈",
    desc: "쌀, 물, 누룩만으로 빚은 사케로 쌀 본연의 감칠맛과 깊은 풍미가 특징입니다.",
    representative: "키쿠스이 순미 클래식, 난부비진 특별순미주",
    method: "쌀의 풍미가 살아있고 깔끔한 드라이 타입이다. <br>두 제품 모두 전통적인 순미주의 단맛 없이 깔끔하고 건조한 마무리가 돋보인다.",
    image: "https://shopping-phinf.pstatic.net/main_5498600/54986002584.jpg"
  },
  honjozo: {
    title: "本醸造 | 혼조조",
    desc: "소량의 양조 알코올을 첨가해 깔끔하고 드라이한 맛을 강조한 사케입니다.",
    representative: "겐비시 쿠로마츠 혼조조, 미치노쿠 오니코로시 혼조",
    method: "소량의 양조용 알코올을 더해 깔끔함과 가벼움을 강조한다. <br>전체적으로 두 제품 모두 쌉싸래한 끝맛이 돋보이는 드라이한 스타일이다.",
    image: "https://shopping-phinf.pstatic.net/main_5538296/55382969649.jpg" 
  },
  ginjo: {
    title: "吟醸 | 긴조",
    desc: "쌀을 60% 이하로 정미하고 저온 발효하여 과일 향이 화사하게 살아있는 사케입니다.",
    representative: "쿠보타 센주 준마이 긴조, 하쿠츠루 준마이 긴조",
    method: "정제된 향과 깔끔함이 특징이다. <br>두 제품 모두 가벼운 바디와 깨끗한 뒷맛으로 서빙 온도에 따라 다양한 변화가 즐겁다.",
    image: "https://shopping-phinf.pstatic.net/main_5775537/57755379818.jpg" 
  },
  daiginjo: {
    title: "大吟醸 | 다이긴조",
    desc: "50% 이하의 정미율을 사용한 최고급 사케로 섬세하고 우아한 향과 투명한 맛을 지닙니다.",
    representative: "쿠보타 만쥬 준마이다이긴조,<br> 닷사이 23 준마이다이긴조,<br> 하카이산 준마이다이긴조",
    method: "쌀을 50% 이하로 정밀하게 도정하여 매우 깨끗하고 화려한 향을 낸다. <br>셋 다 우아하고 선명한 과일·꽃향을 지녔으며, 드라이한 뒷맛이 두드러진다.",
    image: "https://shopping-phinf.pstatic.net/main_4517606/45176069708.1.jpg" 
  },
  futsu: {
    title: "普通酒 | 후츠슈",
    desc: "일상적으로 즐기기 위한 사케로 가격과 접근성이 뛰어나며 일본에서 가장 많이 소비됩니다.",
    representative: "하쿠츠루 사케, 월계관 사케",
    method: "저가의 일상용 주정주(혼합주)로, 법적 구분 없이 양조주를 첨가하여 제조한다. <br>두 제품 모두 가볍고 마시기 편하며, 차게 해도 온전히 마셔도 부담이 적다.",
    image: "https://shopping-phinf.pstatic.net/main_4793225/47932258618.20240523160547.jpg" 
  },
}

function setSake(type) {
  const data = sakeData[type];
  
  // 텍스트 업데이트
  document.getElementById("sakeText").innerHTML = `
    <h2>${data.title}</h2>
    <p class="desc">${data.desc}</p>
    <div class="sake-info">
      <div class="info-item">
        <strong>대표 명주</strong>
        <span>${data.representative}</span>
      </div>
      <div class="info-item">
        <strong>느낌</strong>
        <span>${data.method}</span>
      </div>
    </div>
  `;

  // 이미지 업데이트
  const imgElement = document.getElementById("sakeImage");
  if(imgElement) {
    imgElement.src = data.image;
    imgElement.alt = data.title;
  }
}

setSake("junmai");

document.querySelectorAll("#typeTabs li").forEach(tab => {
  tab.onclick = () => {
    document.querySelectorAll("#typeTabs li").forEach(t => t.classList.remove("active"));
    tab.classList.add("active");
    setSake(tab.dataset.type);
  };
});
</script>

<?php include './include/footer.php'; ?>
