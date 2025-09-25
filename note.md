# 📚 HTML & CSS 語法完整筆記

## 目錄
- [HTML 語法篇](#html-語法篇)
- [CSS 語法篇](#css-語法篇)
- [響應式設計](#響應式設計)
- [實用技巧](#實用技巧)

---

## 🏗️ HTML 語法篇

### 1. 基本文檔結構
```html
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>頁面標題</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- 頁面內容 -->
</body>
</html>
```

**語法說明：**
- `<!DOCTYPE html>`：HTML5 文檔類型聲明，告訴瀏覽器使用HTML5標準
- `lang="zh-TW"`：指定頁面語言為繁體中文，有助於SEO和輔助技術
- `<meta charset="UTF-8">`：設定字符編碼，確保中文正確顯示
- `<meta name="viewport">`：響應式設計的關鍵設定，控制頁面在行動裝置上的顯示

### 2. 語義化標籤結構
```html
<!-- 導覽區域 -->
<nav class="navbar">
    <div class="nav-container">
        <div class="logo">網站名稱</div>
        <ul class="nav-menu">
            <li><a href="#home">首頁</a></li>
            <li><a href="#about">關於我們</a></li>
        </ul>
    </div>
</nav>

<!-- 主要內容區域 -->
<main>
    <!-- 頁首橫幅 -->
    <header class="header">
        <div class="header-container">
            <h1>主標題</h1>
            <p>描述文字</p>
            <button class="btn-register">立即註冊</button>
        </div>
    </header>
    
    <!-- 內容區段 -->
    <section class="content">
        <article class="content-row">
            <div class="content-image">
                <img src="image.jpg" alt="圖片描述">
            </div>
            <div class="content-text">
                <h2>區段標題</h2>
                <p>內容段落</p>
            </div>
        </article>
    </section>
</main>

<!-- 頁尾 -->
<footer class="footer">
    <p>&copy; 2024 版權聲明</p>
</footer>
```

**語義化標籤用途：**
- `<nav>`：導覽區域，包含網站選單
- `<header>`：頁首區域，通常包含標題和重要信息
- `<main>`：主要內容區域，每頁只能有一個
- `<section>`：內容區段，具有主題性的內容群組
- `<article>`：獨立的內容文章或區塊
- `<aside>`：側邊欄或輔助內容
- `<footer>`：頁尾區域，包含版權、連結等信息

### 3. 表單元素
```html
<form id="contactForm" class="form-container">
    <div class="form-group">
        <label for="name">姓名：</label>
        <input type="text" id="name" name="name" required>
    </div>
    
    <div class="form-group">
        <label for="email">電子郵件：</label>
        <input type="email" id="email" name="email" required>
    </div>
    
    <div class="form-group">
        <label for="message">訊息：</label>
        <textarea id="message" name="message" rows="5" placeholder="請輸入您的訊息..."></textarea>
    </div>
    
    <div class="form-group">
        <label>
            <input type="checkbox" name="subscribe" value="yes">
            訂閱電子報
        </label>
    </div>
    
    <button type="submit" class="btn-submit">送出</button>
</form>
```

**表單屬性說明：**
- `for` 屬性：連結 label 和 input，提升可訪問性
- `required`：HTML5 表單驗證，必填欄位
- `type="email"`：自動驗證電子郵件格式
- `placeholder`：輸入提示文字
- `rows`：文字區域的行數

### 4. 常用HTML屬性

#### 全域屬性
```html
<div id="unique-id" class="style-class" data-custom="value">
    內容
</div>
```

- `id`：唯一標識符
- `class`：CSS類別名稱（可多個）
- `data-*`：自定義資料屬性

#### 連結和媒體
```html
<!-- 連結 -->
<a href="https://example.com" target="_blank" rel="noopener">外部連結</a>
<a href="#section" title="跳到區段">內部錨點</a>

<!-- 圖片 -->
<img src="image.jpg" alt="圖片描述" width="300" height="200">

<!-- 影片 -->
<video controls width="400">
    <source src="movie.mp4" type="video/mp4">
    您的瀏覽器不支援影片標籤
</video>
```

---

## 🎨 CSS 語法篇

### 1. 選擇器類型

#### 基本選擇器
```css
/* 元素選擇器 - 選擇所有指定元素 */
body {
    font-family: 'Microsoft JhengHei', Arial, sans-serif;
    line-height: 1.6;
}

/* 類別選擇器 - 選擇具有指定class的元素 */
.navbar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 15px 0;
}

/* ID選擇器 - 選擇具有指定id的元素 */
#loginForm {
    max-width: 400px;
    margin: 0 auto;
}

/* 屬性選擇器 - 選擇具有指定屬性的元素 */
input[type="text"] {
    border: 1px solid #ddd;
    padding: 10px;
}

/* 偽類選擇器 - 選擇元素的特定狀態 */
a:hover {
    opacity: 0.8;
}

button:focus {
    outline: 2px solid #007bff;
}
```

#### 組合選擇器
```css
/* 後代選擇器 - 選擇指定元素內的所有後代元素 */
.navbar a {
    color: white;
    text-decoration: none;
}

/* 子選擇器 - 只選擇直接子元素 */
.nav-menu > li {
    margin-left: 30px;
}

/* 相鄰兄弟選擇器 - 選擇緊接著的兄弟元素 */
h1 + p {
    margin-top: 0;
}

/* 群組選擇器 - 同時選擇多個元素 */
h1, h2, h3 {
    font-weight: bold;
    color: #333;
}
```

### 2. 盒模型 (Box Model)
```css
.content-text {
    /* 盒模型屬性 */
    width: 60%;                    /* 寬度 */
    height: auto;                  /* 高度：自動調整 */
    padding: 40px;                 /* 內邊距：內容與邊框的距離 */
    margin: 20px auto;             /* 外邊距：上下20px，左右自動置中 */
    border: 1px solid #ddd;        /* 邊框：寬度 樣式 顏色 */
    
    /* 盒模型計算方式 */
    box-sizing: border-box;        /* 邊框和內邊距包含在總寬度內 */
}
```

**盒模型示意圖：**
```
┌─────────────────── margin ───────────────────┐
│  ┌───────────────── border ─────────────────┐  │
│  │  ┌─────────────── padding ─────────────┐  │  │
│  │  │                                     │  │  │
│  │  │            content                  │  │  │
│  │  │                                     │  │  │
│  │  └─────────────────────────────────────┘  │  │
│  └───────────────────────────────────────────┘  │
└─────────────────────────────────────────────────┘
```

### 3. 彈性盒子佈局 (Flexbox)
```css
.content-row {
    display: flex;                    /* 啟用彈性盒子佈局 */
    
    /* 主軸對齊方式 */
    justify-content: space-between;   /* 兩端對齊 */
    /* 其他選項：
       flex-start    - 起始對齊
       flex-end      - 結束對齊
       center        - 置中對齊
       space-around  - 環繞對齊
       space-evenly  - 等間距對齊 */
    
    /* 交叉軸對齊方式 */
    align-items: center;              /* 垂直置中 */
    /* 其他選項：
       flex-start    - 頂部對齊
       flex-end      - 底部對齊
       stretch       - 拉伸填滿
       baseline      - 基線對齊 */
    
    /* 排列方向 */
    flex-direction: row;              /* 水平排列 */
    /* 其他選項：
       column        - 垂直排列
       row-reverse   - 水平反向
       column-reverse - 垂直反向 */
    
    /* 換行設定 */
    flex-wrap: wrap;                  /* 允許換行 */
    /* 其他選項：
       nowrap        - 不換行
       wrap-reverse  - 反向換行 */
}

/* 彈性項目屬性 */
.flex-item {
    flex: 1;                          /* 簡寫：flex-grow flex-shrink flex-basis */
    flex-grow: 1;                     /* 增長因子：分配剩餘空間的比例 */
    flex-shrink: 1;                   /* 收縮因子：空間不足時的收縮比例 */
    flex-basis: 0;                    /* 基準大小：分配剩餘空間前的初始大小 */
}
```

### 4. 網格佈局 (Grid Layout)
```css
.grid-container {
    display: grid;
    
    /* 定義行和列 */
    grid-template-columns: 1fr 2fr 1fr;      /* 三列：1:2:1 比例 */
    grid-template-rows: auto 1fr auto;       /* 三行：自動 彈性 自動 */
    
    /* 間距設定 */
    gap: 20px;                               /* 行列間距 */
    grid-column-gap: 20px;                   /* 列間距 */
    grid-row-gap: 10px;                      /* 行間距 */
    
    /* 對齊設定 */
    justify-items: center;                   /* 水平對齊 */
    align-items: center;                     /* 垂直對齊 */
}

.grid-item {
    /* 項目定位 */
    grid-column: 1 / 3;                      /* 佔據第1到第3列 */
    grid-row: 2 / 4;                         /* 佔據第2到第4行 */
    
    /* 或使用命名區域 */
    grid-area: header;                       /* 佔據名為header的區域 */
}
```

### 5. 漸層背景
```css
.header {
    /* 線性漸層 */
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    /* 參數說明：
       135deg    - 漸層角度（45度的倍數較常用）
       #f093fb 0% - 起始顏色和位置
       #f5576c 100% - 結束顏色和位置 */
    
    /* 徑向漸層 */
    background: radial-gradient(circle, #ff6b6b, #ee5a24);
    /* 參數說明：
       circle    - 形狀（circle圓形 / ellipse橢圓）
       #ff6b6b   - 中心顏色
       #ee5a24   - 邊緣顏色 */
    
    /* 多重漸層 */
    background: 
        linear-gradient(45deg, rgba(255,0,0,0.5), transparent),
        linear-gradient(135deg, #667eea, #764ba2),
        url('background.jpg');
    /* 多個背景層疊，前面的在上層 */
}
```

**常用漸層角度：**
- `0deg` - 從下到上
- `90deg` - 從左到右
- `180deg` - 從上到下
- `270deg` - 從右到左
- `45deg` - 左下到右上
- `135deg` - 左上到右下

### 6. 陰影效果
```css
.content-image img {
    /* 盒陰影：x偏移 y偏移 模糊半徑 擴散半徑 顏色 */
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    
    /* 多重陰影 */
    box-shadow: 
        0 2px 5px rgba(0,0,0,0.1),        /* 近距陰影 */
        0 10px 30px rgba(0,0,0,0.2);      /* 遠距陰影 */
    
    /* 內陰影 */
    box-shadow: inset 0 2px 5px rgba(0,0,0,0.1);
}

.text-shadow-example {
    /* 文字陰影：x偏移 y偏移 模糊半徑 顏色 */
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    
    /* 多重文字陰影 */
    text-shadow: 
        1px 1px 2px rgba(0,0,0,0.3),
        3px 3px 6px rgba(0,0,0,0.1);
}
```

### 7. 轉換和動畫
```css
.btn-register {
    /* 過渡動畫：屬性 持續時間 時間函數 延遲時間 */
    transition: all 0.3s ease 0s;
    
    /* 分別設定不同屬性的過渡 */
    transition: 
        background 0.3s ease,
        transform 0.2s cubic-bezier(0.4, 0, 0.2, 1),
        box-shadow 0.3s ease;
    
    /* 2D 轉換 */
    transform: translateY(0);
}

.btn-register:hover {
    /* 懸停時的轉換 */
    transform: translateY(-2px) scale(1.05);
    
    /* 各種轉換函數 */
    transform: 
        translateX(10px)      /* X軸移動 */
        translateY(-5px)      /* Y軸移動 */
        rotate(45deg)         /* 旋轉 */
        scale(1.1)           /* 縮放 */
        skew(15deg, 0deg);   /* 傾斜 */
}

/* 關鍵幀動畫 */
@keyframes fadeIn {
    0% { 
        opacity: 0; 
        transform: translateY(20px);
    }
    100% { 
        opacity: 1; 
        transform: translateY(0);
    }
}

.animated-element {
    /* 使用關鍵幀動畫：名稱 持續時間 時間函數 延遲 重複次數 方向 填充模式 播放狀態 */
    animation: fadeIn 1s ease-in-out 0s 1 normal forwards running;
    
    /* 簡寫 */
    animation: fadeIn 1s ease-in-out;
}
```

**常用時間函數：**
- `ease` - 慢-快-慢
- `ease-in` - 慢-快
- `ease-out` - 快-慢
- `ease-in-out` - 慢-快-慢（比ease更明顯）
- `linear` - 等速
- `cubic-bezier()` - 自定義貝茲曲線

### 8. CSS 變數 (Custom Properties)
```css
:root {
    /* 定義全域變數 */
    --primary-color: #667eea;
    --secondary-color: #764ba2;
    --accent-color: #ff6b6b;
    
    --font-size-large: 48px;
    --font-size-medium: 24px;
    --font-size-small: 16px;
    
    --spacing-large: 40px;
    --spacing-medium: 20px;
    --spacing-small: 10px;
    
    --border-radius: 15px;
    --box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.header {
    /* 使用變數 */
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    padding: var(--spacing-large) 0;
    border-radius: var(--border-radius);
}

/* 在特定元件中覆蓋變數 */
.dark-theme {
    --primary-color: #2c3e50;
    --secondary-color: #34495e;
}
```

---

## 📱 響應式設計

### 1. 媒體查詢 (Media Queries)
```css
/* 基本媒體查詢 */
@media (max-width: 900px) {
    .navbar {
        padding: 10px 0;
    }
}

/* 複合條件 */
@media (max-width: 900px) and (min-width: 751px) {
    .content {
        max-width: 100%;
        padding: 40px 20px;
    }
}

/* 方向查詢 */
@media (orientation: landscape) {
    /* 橫向螢幕樣式 */
}

@media (orientation: portrait) {
    /* 直向螢幕樣式 */
}

/* 解析度查詢 */
@media (-webkit-min-device-pixel-ratio: 2) {
    /* 高解析度螢幕（Retina等） */
}

/* 裝置類型 */
@media screen {
    /* 螢幕裝置 */
}

@media print {
    /* 列印樣式 */
    body { color: black; }
    .navbar { display: none; }
}
```

### 2. 常用斷點
```css
/* 手機 */
@media (max-width: 480px) { 
    /* 手機專用樣式 */
}

/* 平板直向 */
@media (max-width: 768px) { 
    /* 平板直向樣式 */
}

/* 平板橫向 / 小筆電 */
@media (max-width: 1024px) { 
    /* 平板橫向樣式 */
}

/* 桌機 */
@media (min-width: 1025px) { 
    /* 桌機樣式 */
}
```

### 3. 行動優先設計
```css
/* 預設樣式（行動裝置） */
.container {
    width: 100%;
    padding: 10px;
}

/* 平板樣式 */
@media (min-width: 768px) {
    .container {
        max-width: 750px;
        margin: 0 auto;
        padding: 20px;
    }
}

/* 桌面樣式 */
@media (min-width: 1024px) {
    .container {
        max-width: 1200px;
        padding: 40px;
    }
}
```

---

## 🔧 實用技巧

### 1. 置中對齊方法
```css
/* 方法1：Flexbox 置中 */
.flex-center {
    display: flex;
    justify-content: center;
    align-items: center;
}

/* 方法2：絕對定位置中 */
.absolute-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

/* 方法3：Grid 置中 */
.grid-center {
    display: grid;
    place-items: center;
}

/* 方法4：文字置中 */
.text-center {
    text-align: center;
    line-height: 100px; /* 單行文字垂直置中 */
}
```

### 2. 圖片處理
```css
.responsive-image {
    width: 100%;
    height: auto;              /* 保持比例 */
    object-fit: cover;         /* 裁切填滿 */
    object-position: center;   /* 裁切位置 */
}

/* 圖片濾鏡效果 */
.image-effects {
    filter: 
        brightness(1.2)        /* 亮度 */
        contrast(1.1)          /* 對比度 */
        saturate(1.3)          /* 飽和度 */
        blur(2px)              /* 模糊 */
        grayscale(0.5);        /* 灰階 */
}
```

### 3. 文字處理
```css
/* 單行文字截斷 */
.text-overflow {
    white-space: nowrap;       /* 不換行 */
    overflow: hidden;          /* 隱藏溢出 */
    text-overflow: ellipsis;   /* 顯示省略號 */
}

/* 多行文字截斷 */
.multiline-ellipsis {
    display: -webkit-box;
    -webkit-line-clamp: 3;     /* 限制行數 */
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* 文字裝飾 */
.text-decorations {
    text-decoration: underline overline line-through;
    text-decoration-color: red;
    text-decoration-style: wavy;
    text-decoration-thickness: 2px;
}
```

### 4. 彈性網格系統
```css
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.flex-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.flex-grid > * {
    flex: 1 1 300px; /* grow shrink basis */
}
```

### 5. 現代 CSS 功能

#### 容器查詢 (Container Queries)
```css
.card-container {
    container-type: inline-size;
}

@container (min-width: 400px) {
    .card {
        display: flex;
    }
}
```

#### 邏輯屬性 (Logical Properties)
```css
.element {
    margin-inline: auto;        /* 相當於 margin-left: auto; margin-right: auto; */
    padding-block: 20px;        /* 相當於 padding-top: 20px; padding-bottom: 20px; */
    border-inline-start: 2px solid red;  /* 依據書寫方向的起始邊框 */
}
```

#### CSS Subgrid
```css
.parent-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
}

.child-grid {
    display: grid;
    grid-column: 1 / -1;
    grid-template-columns: subgrid;  /* 繼承父網格的列定義 */
}
```

---

## 📋 最佳實踐

### 1. CSS 架構
```css
/* 1. 重置和基礎樣式 */
*,
*::before,
*::after {
    box-sizing: border-box;
}

/* 2. 變數定義 */
:root {
    --primary-color: #007bff;
    --font-family: system-ui, sans-serif;
}

/* 3. 全域樣式 */
body {
    font-family: var(--font-family);
    line-height: 1.6;
}

/* 4. 元件樣式 */
.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.25rem;
}

/* 5. 工具類別 */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
}
```

### 2. 命名規範 (BEM)
```css
/* Block（區塊） */
.card { }

/* Element（元素） */
.card__title { }
.card__content { }

/* Modifier（修飾符） */
.card--featured { }
.card__title--large { }
```

### 3. 效能優化
```css
/* 使用 transform 和 opacity 進行動畫 */
.smooth-animation {
    will-change: transform, opacity;
    transition: transform 0.3s ease, opacity 0.3s ease;
}

/* 避免重複計算 */
.efficient-layout {
    contain: layout style paint;
}
```

---

## 📖 參考資源

### 官方文檔
- [MDN Web Docs - HTML](https://developer.mozilla.org/zh-TW/docs/Web/HTML)
- [MDN Web Docs - CSS](https://developer.mozilla.org/zh-TW/docs/Web/CSS)
- [W3C HTML Specification](https://html.spec.whatwg.org/)
- [W3C CSS Specification](https://www.w3.org/Style/CSS/)

### 實用工具
- [Can I Use](https://caniuse.com/) - 瀏覽器支援查詢
- [CSS Grid Generator](https://cssgrid-generator.netlify.app/) - CSS Grid 生成器
- [Flexbox Froggy](https://flexboxfroggy.com/) - Flexbox 學習遊戲
- [CSS Gradient](https://cssgradient.io/) - 漸層生成器

### 設計系統
- [Bootstrap](https://getbootstrap.com/) - 響應式框架
- [Tailwind CSS](https://tailwindcss.com/) - 工具優先框架
- [Material Design](https://material.io/) - Google 設計系統

---

**筆記完成日期：** 2024年9月25日  
**版本：** 1.0  
**作者：** ITSA Team 021  

這份筆記涵蓋了現代網頁開發中最重要的HTML和CSS語法，結合實際專案範例，適合初學者學習和開發者參考使用。