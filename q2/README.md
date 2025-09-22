# 圖書管理系統

## 專案說明

本專案為完整的圖書管理系統，提供圖書的增刪改查功能，使用純前端技術實現，數據持久化採用瀏覽器 LocalStorage。

## 功能特色

### 📚 核心功能

- **新增圖書**: 輸入書名、作者、分類、出版年份等資訊
- **查看圖書**: 清晰的表格顯示所有圖書資訊
- **編輯圖書**: 即時修改圖書資訊
- **刪除圖書**: 安全的刪除確認機制
- **搜尋功能**: 支援書名、作者、分類關鍵字搜尋
- **分類篩選**: 快速篩選特定分類的圖書
- **統計資訊**: 即時顯示圖書總數和分類統計

### 🎨 介面設計

- **現代化 UI**: 採用卡片式設計和柔和配色
- **響應式布局**: 完美適配桌面和行動裝置
- **直觀操作**: 使用者友善的互動設計
- **視覺回饋**: 清晰的狀態提示和動畫效果

### 💾 資料管理

- **本地儲存**: 使用 LocalStorage 持久化資料
- **資料驗證**: 完整的輸入驗證機制
- **即時更新**: 操作後立即更新顯示
- **資料備份**: 瀏覽器本地資料永久保存

## 檔案結構

```
q2/
├── q2.html          # 主要應用程式
├── q2.css           # 樣式表檔案
└── README.md        # 專案說明文件
```

## 技術架構

### 前端技術棧

- **HTML5**: 語意化標籤和現代結構
- **CSS3**: 彈性布局、動畫效果、響應式設計
- **JavaScript ES6+**: 模組化程式設計、箭頭函數、解構賦值
- **LocalStorage API**: 瀏覽器端資料持久化

### CSS 架構特點

```css
/* 變數定義 */
:root {
  --primary-color: #4a90e2;
  --secondary-color: #7bb3f0;
  --success-color: #28a745;
  --danger-color: #dc3545;
  --light-bg: #f8f9fa;
}

/* 彈性布局 */
.form-row {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

/* 響應式設計 */
@media (max-width: 768px) {
  .form-row {
    flex-direction: column;
  }
}
```

### JavaScript 架構特點

```javascript
// 模組化設計
class BookManager {
    constructor() {
        this.books = this.loadBooks();
        this.initializeEventListeners();
        this.displayBooks();
    }

    // 核心方法
    addBook(book) { ... }
    editBook(id, updatedBook) { ... }
    deleteBook(id) { ... }
    searchBooks(query) { ... }
}

// 資料持久化
saveBooks() {
    localStorage.setItem('books', JSON.stringify(this.books));
}

loadBooks() {
    const saved = localStorage.getItem('books');
    return saved ? JSON.parse(saved) : [];
}
```

## 功能詳細說明

### 📖 新增圖書

1. 填寫圖書資訊表單
2. 系統自動驗證必填欄位
3. 成功新增後立即顯示在列表中
4. 資料自動保存到 LocalStorage

### 🔍 搜尋功能

- **即時搜尋**: 輸入即時更新結果
- **多欄位搜尋**: 支援書名、作者、分類搜尋
- **模糊匹配**: 不需要完全匹配關鍵字
- **搜尋高亮**: 搜尋結果關鍵字高亮顯示

### 🏷️ 分類管理

- **預設分類**: 文學、科學、歷史、藝術、技術、其他
- **快速篩選**: 點擊分類按鈕即時篩選
- **分類統計**: 顯示各分類圖書數量
- **全部顯示**: 一鍵顯示所有圖書

### ✏️ 編輯功能

1. 點擊編輯按鈕進入編輯模式
2. 表單自動填入現有資料
3. 修改後點擊更新完成編輯
4. 支援取消編輯恢復原狀態

### 🗑️ 刪除功能

- **安全確認**: 刪除前顯示確認對話框
- **即時更新**: 刪除後立即更新顯示
- **資料同步**: 自動同步到 LocalStorage
- **無法復原**: 提醒使用者刪除的永久性

## 資料結構

### 圖書物件結構

```javascript
{
    id: 1633024800000,           // 時間戳記作為唯一ID
    title: "JavaScript權威指南",    // 書名
    author: "David Flanagan",     // 作者
    category: "技術",             // 分類
    year: 2021,                  // 出版年份
    isbn: "978-1234567890",      // ISBN (選填)
    notes: "經典程式設計書籍"       // 備註 (選填)
}
```

### LocalStorage 資料格式

```json
{
  "books": [
    {
      "id": 1633024800000,
      "title": "JavaScript權威指南",
      "author": "David Flanagan",
      "category": "技術",
      "year": 2021,
      "isbn": "978-1234567890",
      "notes": "經典程式設計書籍"
    }
  ]
}
```

## 響應式設計

### 斷點設計

```css
/* 桌面版 (>768px) */
.form-row {
  display: flex;
  gap: 1rem;
}

/* 平板和手機 (≤768px) */
@media (max-width: 768px) {
  .form-row {
    flex-direction: column;
  }

  .books-table {
    font-size: 0.9rem;
  }

  .btn {
    padding: 0.5rem 1rem;
  }
}

/* 小螢幕手機 (≤480px) */
@media (max-width: 480px) {
  .container {
    padding: 1rem;
  }

  .books-table th,
  .books-table td {
    padding: 0.5rem;
  }
}
```

### 適應性元素

- **彈性表格**: 小螢幕時自動調整欄位寬度
- **響應式按鈕**: 觸控友善的按鈕尺寸
- **適應性字體**: 根據螢幕大小調整字體
- **智慧排版**: 自動調整表單布局

## 瀏覽器相容性

### 支援瀏覽器

- ✅ Chrome 60+
- ✅ Firefox 60+
- ✅ Safari 12+
- ✅ Edge 79+
- ✅ 行動版 Safari (iOS 12+)
- ✅ 行動版 Chrome (Android 8+)

### 功能支援

- ✅ LocalStorage API
- ✅ ES6+ JavaScript
- ✅ CSS Grid/Flexbox
- ✅ CSS 變數
- ✅ 媒體查詢

## 使用說明

### 快速開始

1. 直接在瀏覽器開啟 `q2.html`
2. 開始新增您的第一本圖書
3. 所有資料會自動保存在瀏覽器中

### 操作流程

1. **新增圖書**
   - 填寫表單資訊
   - 點擊「新增圖書」按鈕
2. **搜尋圖書**
   - 在搜尋框輸入關鍵字
   - 結果即時顯示
3. **篩選圖書**
   - 點擊分類按鈕快速篩選
   - 點擊「全部」顯示所有圖書
4. **編輯圖書**
   - 點擊表格中的「編輯」按鈕
   - 修改資訊後點擊「更新」
5. **刪除圖書**
   - 點擊「刪除」按鈕
   - 確認後完成刪除

### 資料管理

- **備份資料**: 瀏覽器開發者工具 → Application → Local Storage
- **清除資料**: 清除瀏覽器資料或手動刪除 LocalStorage
- **匯出資料**: 可從開發者工具複製 JSON 資料

## 開發亮點

### 程式設計特色

- **物件導向**: 使用 ES6 Class 組織程式碼
- **事件驅動**: 完整的事件處理機制
- **資料驗證**: 多層級的輸入驗證
- **錯誤處理**: 優雅的錯誤處理和使用者提示

### 使用者體驗

- **即時回饋**: 操作後立即顯示結果
- **視覺提示**: 清晰的成功/錯誤提示
- **流暢動畫**: 平滑的過渡效果
- **直觀操作**: 符合使用者習慣的操作邏輯

### 效能最佳化

- **快速渲染**: 最佳化的 DOM 操作
- **記憶體管理**: 適當的事件監聽器管理
- **資料快取**: 有效的 LocalStorage 使用
- **響應速度**: 即時的使用者互動回應

---

**專案完成**: 2024 年 9 月 22 日  
**開發者**: Team 021  
**競賽**: ITSA 全國大專校院程式設計極客挑戰賽
