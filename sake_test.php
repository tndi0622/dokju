<?php
include './include/header.php';
include './include/db_connect.php';

// Fetch recommended products for each category
$recommends = [
    'junmai' => [],
    'daiginjo' => [],
    'honjozo' => [],
    'futsushu' => []
];

// Map internal keys to DB category keywords
$keywords = [
    'junmai' => '준마이',
    'daiginjo' => '다이긴조',
    'honjozo' => '혼조조',
    'futsushu' => '후츠슈'
];

foreach ($keywords as $key => $word) {
    // Get 3 random items per category
    $sql = "SELECT id, product_name, image, price, type FROM products WHERE type LIKE '%$word%' ORDER BY RAND() LIMIT 3";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Set default image if empty
            if(empty($row['image'])) $row['image'] = '/dokju/images/sake_bottle.jpg';
            $recommends[$key][] = $row;
        }
    }
}

// Pass PHP data to JS
$recommend_json = json_encode($recommends, JSON_UNESCAPED_UNICODE);
?>
<link rel="stylesheet" href="/dokju/css/sake_test.css?v=<?php echo time(); ?>">

<div class="test-container" id="quiz-screen">
    <div class="test-header">
        <h2>사케 취향 테스트</h2>
        <p>몇 가지 질문으로 내 입맛에 딱 맞는 사케를 찾아보세요 🍶</p>
    </div>

    <div class="progress-bar">
        <div class="progress-fill" id="progress"></div>
    </div>

    <div id="question-container">
        <!-- Questions -->
    </div>
    
    <div class="test-footer">
        ※ 직관적으로 빠르게 선택하는 것이 정확합니다.
    </div>
</div>

<div class="test-container result-container" id="result-screen">
    <div class="result-content">
        <div class="result-type" id="result-subtitle">TYPE A</div>
        <h1 class="result-title" id="result-title">당신은...</h1>
        
        <div class="result-desc" id="result-desc">
            결과 설명...
        </div>

        <div style="margin-bottom:40px;">
            <p style="margin-bottom:20px; font-weight:600; color:#888;">회원님을 위한 맞춤 추천</p>
            <div id="recommend-products" class="rec-grid">
                <!-- Products injected here -->
            </div>
            <div id="no-products" style="display:none; color:#999; margin-bottom:20px;">
                현재 추천 상품 재고가 부족합니다 😢<br>샵에서 더 많은 상품을 확인해보세요.
            </div>
        </div>

        <div class="btn-group">
            <button class="btn-restart" onclick="location.reload()">다시 하기</button>
            <a href="/dokju/shop.php" class="btn-shop">전체 상품 보기</a>
        </div>
    </div>
</div>

<script>
// Load PHP Data
const recommendData = <?php echo $recommend_json; ?>;

// Question Logic
// Types: junmai(쌀/담백), daiginjo(향/과일), honjozo(가성비/깔끔), futsushu(데일리/편안)
const questions = [
    {
        q: "Q1. 오늘 마실 술, 어떤 분위기였으면 좋겠나요?",
        a: [
            { text: "특별한 날, 고급스럽고 우아하게", type: ["daiginjo", "junmai"] },
            { text: "퇴근 후 집에서, 편안하고 부담 없이", type: ["futsushu", "honjozo"] }
        ]
    },
    {
        q: "Q2. 평소 안주 취향은?",
        a: [
            { text: "회나 샐러드처럼 가볍고 신선한 음식", type: ["daiginjo", "honjozo"] },
            { text: "나베나 꼬치처럼 맛이 진하고 따뜻한 음식", type: ["junmai", "futsushu"] }
        ]
    },
    {
        q: "Q3. 술에서 가장 중요하게 생각하는 것은?",
        a: [
            { text: "입안 가득 퍼지는 화려한 향기", type: ["daiginjo"] },
            { text: "목넘김이 깔끔하고 뒷맛이 개운한 것", type: ["honjozo", "junmai"] }
        ]
    },
    {
        q: "Q4. 술의 가격대에 대한 생각은?",
        a: [
            { text: "맛만 있다면 비싸도 괜찮아 (플렉스!)", type: ["daiginjo", "junmai"] },
            { text: "가성비가 좋아야 자주 마시지 (합리적)", type: ["honjozo", "futsushu"] }
        ]
    },
    {
        q: "Q5. 어떤 온도에서 마시는 걸 좋아하시나요?",
        a: [
            { text: "차갑게 칠링해서 와인처럼", type: ["daiginjo", "honjozo"] },
            { text: "따뜻하게 데워서(아츠캉) 온몸이 녹게", type: ["junmai", "honjozo", "futsushu"] }
        ]
    },
    {
        q: "Q6. 커피나 차를 마실 때 선호하는 스타일은?",
        a: [
            { text: "산미가 있고 향긋한 스타일 (플로럴)", type: ["daiginjo"] },
            { text: "구수하고 묵직한 바디감 (고소함)", type: ["junmai", "futsushu"] }
        ]
    },
    {
        q: "Q7. 알코올 도수는 어떤 게 좋나요?",
        a: [
            { text: "부드러워서 술술 넘어가는 낮은 도수 느낌", type: ["junmai", "daiginjo"] },
            { text: "어느 정도 술 마신 기분이 드는 짜릿한 느낌", type: ["honjozo", "futsushu"] }
        ]
    },
    {
        q: "Q8. '쌀' 본연의 맛을 좋아하시나요?",
        a: [
            { text: "네! 밥맛처럼 구수한 감칠맛이 최고죠", type: ["junmai"] },
            { text: "아뇨, 술은 맑고 깨끗해야죠", type: ["daiginjo", "honjozo"] }
        ]
    },
    {
        q: "Q9. 마지막으로, 당신의 성향은?",
        a: [
            { text: "새로운 맛과 향을 탐험하는 미식가", type: ["daiginjo", "junmai"] },
            { text: "익숙하고 편안한 맛을 즐기는 애주가", type: ["futsushu", "honjozo"] }
        ]
    }
];

let currentStep = 0;
let scores = {
    junmai: 0,
    daiginjo: 0,
    honjozo: 0,
    futsushu: 0
};

const qContainer = document.getElementById('question-container');
const progress = document.getElementById('progress');

function init() {
    renderQuestion();
    updateProgress();
}

function renderQuestion() {
    qContainer.innerHTML = '';
    const q = questions[currentStep];
    
    // Check if questions exist
    if(!q) return;

    const div = document.createElement('div');
    div.className = 'question-box active';
    
    let html = `<span class="question-num">QUESTION ${currentStep + 1}</span>`;
    html += `<h3 class="question-text">${q.q}</h3>`;
    
    q.a.forEach((ans) => {
        // Encode types array to string for passing
        const typesStr = JSON.stringify(ans.type);
        html += `<button class="answer-btn" onclick='nextStep(${typesStr})'>${ans.text}</button>`;
    });
    
    div.innerHTML = html;
    qContainer.appendChild(div);
}

function nextStep(types) {
    // Add weights
    types.forEach(t => {
        if(scores[t] !== undefined) scores[t] += 1;
    });
    
    currentStep++;
    
    if (currentStep < questions.length) {
        renderQuestion();
        updateProgress();
    } else {
        showResult();
    }
}

function updateProgress() {
    const percent = ((currentStep) / questions.length) * 100;
    progress.style.width = percent + '%';
}

function showResult() {
    document.getElementById('quiz-screen').style.display = 'none';
    document.getElementById('result-screen').style.display = 'block';
    
    // Calculate Winner
    // If ties, prioritize daiginjo > junmai > honjozo > futsushu
    let maxScore = -1;
    let maxType = 'junmai';
    
    for (const [key, value] of Object.entries(scores)) {
        if (value > maxScore) {
            maxScore = value;
            maxType = key;
        }
    }
    
    // Result Descriptions
    const results = {
        junmai: {
            sub: "TYPE: JUNMAI",
            title: "진심을 담은 '준마이'",
            desc: "쌀과 물, 누룩으로만 빚어낸 순수한 사케입니다.<br>쌀 본연의 그윽한 감칠맛과 묵직한 바디감이 매력적이죠.<br>식사와 함께 반주로 즐기기에 가장 완벽한 선택입니다.",
            link: "category=준마이"
        },
        daiginjo: {
            sub: "TYPE: DAIGINJO",
            title: "화려한 향기 '다이긴조'",
            desc: "극한으로 도정한 쌀로 빚어내어, 잡미 없이 깨끗하고<br>꽃이나 과일 같은 화려한 향기가 피어오르는 최고급 사케입니다.<br>특별한 날, 와인잔에 담아 향을 음미해보세요.",
            link: "category=다이긴조"
        },
        honjozo: {
            sub: "TYPE: HONJOZO",
            title: "깔끔한 매력 '혼조조'",
            desc: "양조 알코올을 살짝 더해 맛을 경쾌하고 깔끔하게 다듬었습니다.<br>뒷맛이 개운해서 질리지 않고 계속 마실 수 있는 마성의 술이죠.<br>차갑게도, 따뜻하게도 잘 어울리는 만능 사케입니다.",
            link: "category=혼조조"
        },
        futsushu: {
            sub: "TYPE: FUTSU-SHU",
            title: "편안한 친구 '후츠슈'",
            desc: "가장 대중적이고 친근한 사케입니다.<br>복잡한 격식 없이 편안하게, 언제 어디서나 즐길 수 있습니다.<br>퇴근 후 가벼운 한잔으로 하루의 피로를 씻어보세요.",
            link: "category=후츠슈"
        }
    };
    
    const res = results[maxType];
    
    document.getElementById('result-subtitle').innerText = res.sub;
    document.getElementById('result-title').innerText = res.title;
    document.getElementById('result-desc').innerHTML = res.desc;
    
    // Update Shop Link
    const shopBtn = document.querySelector('.btn-shop');
    shopBtn.href = '/dokju/shop.php?' + res.link;
    shopBtn.innerText = `'${res.sub.split(':')[1]}' 전체 보기`;

    // Render Products
    const recContainer = document.getElementById('recommend-products');
    const noProds = document.getElementById('no-products');
    const items = recommendData[maxType];

    if (items && items.length > 0) {
        let html = '';
        items.forEach(item => {
            // Number format helper
            const price = new Intl.NumberFormat('ko-KR').format(item.price);
            html += `
            <a href="/dokju/product_view.php?id=${item.id}" class="rec-card" target="_blank">
                <img src="${item.image}" alt="${item.product_name}" class="rec-img">
                <div class="rec-info">
                    <div class="rec-name">${item.product_name}</div>
                    <div class="rec-price">${price}원</div>
                </div>
            </a>
            `;
        });
        recContainer.innerHTML = html;
    } else {
        recContainer.style.display = 'none';
        noProds.style.display = 'block';
    }
}

init();
</script>

<?php include './include/footer.php'; ?>
