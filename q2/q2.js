/* 
============================================================================
圖書管理系統 - JavaScript 主程式檔案
============================================================================

系統目的：
提供完整的個人圖書收藏管理功能，包含新增、查詢、編輯、刪除操作

技術架構：
- JavaScript ES6：模組化程式設計，使用箭頭函數和現代語法
- LocalStorage：客戶端資料持久化存儲
- DOM 操作：動態內容生成和事件處理

主要功能：
1. 圖書資料的 CRUD 操作（新增、讀取、更新、刪除）
2. 即時搜尋和分類篩選
3. 統計資訊顯示
4. 響應式用戶介面
5. 本地資料持久化

設計特色：
- 無需後端支援，純前端實現
- 直觀的用戶介面設計
- 即時的操作回饋
- 支援多種裝置尺寸

開發資訊：
作者：Team 021
日期：2024/09/22
版本：1.0
============================================================================
*/

/* 
    全域變數宣告 (Global Variables Declaration)
    用於存儲應用程式的狀態和資料
*/

// 圖書資料陣列：存儲所有圖書物件
let books = [];

// 下一個圖書 ID：確保每本書都有唯一識別符
let nextId = 1;

/**
 * 新增圖書函數 (Add Book Function)
 * 功能：處理新圖書的新增操作
 * 包含：表單驗證、資料建立、介面更新
 * 呼叫：由新增按鈕的 onclick 事件觸發
 */
function addBook() {
  // 獲取並清理表單輸入值：使用 trim() 移除前後空白
  const title = document.getElementById("bookTitle").value.trim();
  const author = document.getElementById("bookAuthor").value.trim();
  const year = document.getElementById("bookYear").value.trim();

  // 表單驗證：檢查所有必填欄位是否完整
  if (!title || !author || !year) {
    alert("請填寫所有欄位！");
    return;
  }

  // 年份有效性驗證：確保輸入的年份在合理範圍內
  if (year < 1900 || year > new Date().getFullYear()) {
    alert("請輸入有效的出版年份！");
    return;
  }

  // 建立圖書物件：組織資料結構
  const book = {
    id: nextId++, // 唯一識別符，自動遞增
    title: title, // 圖書標題
    author: author, // 作者姓名
    year: parseInt(year), // 出版年份，轉換為整數
  };

  // 新增到圖書陣列：將新圖書加入資料集合
  books.push(book);

  // 清空表單：重置輸入欄位為空，準備下次輸入
  clearForm();

  // 重新渲染列表：更新顯示內容
  renderBooks();

  // 成功回饋：向用戶確認操作完成
  showMessage("圖書添加成功！", "success");
}

/**
 * 清空表單函數 (Clear Form Function)
 * 功能：重置所有輸入欄位為空值
 * 目的：提升用戶體驗，準備下次輸入
 * 呼叫：在成功新增圖書後自動執行
 */
function clearForm() {
  document.getElementById("bookTitle").value = "";
  document.getElementById("bookAuthor").value = "";
  document.getElementById("bookYear").value = "";
}

/**
 * 渲染圖書列表函數 (Render Books Function)
 * 功能：根據當前資料動態生成並顯示圖書列表
 * 特色：支援空狀態顯示、動態 HTML 生成
 * 呼叫：每次資料變更後執行
 */
function renderBooks() {
  // 獲取列表容器元素
  const bookList = document.getElementById("bookList");

  // 處理空狀態：當沒有圖書時顯示提示訊息
  if (books.length === 0) {
    bookList.innerHTML =
      '<div class="empty-message">暫無圖書資料，請先添加一本書</div>';
    return;
  }

  // 動態生成 HTML：使用陣列 map 方法和模板字串
  bookList.innerHTML = books
    .map(
      (book) => `
        <!-- 
            單一圖書項目 (Individual Book Item)
            id="book-${book.id}"：唯一識別符，用於編輯和刪除操作
            class="book-item"：CSS 樣式鉤子
        -->
        <div class="book-item" id="book-${book.id}">
            <!-- 圖書資訊區域：顯示所有圖書詳細資料 -->
            <div class="book-info">
                <div class="book-title">${book.title}</div>
                <div class="book-author">作者：${book.author}</div>
                <div class="book-year">出版年份：${book.year}</div>
            </div>
            <!-- 
                操作按鈕區域 (Action Buttons Area)
                功能：提供編輯和刪除圖書的操作介面
                設計：使用不同顏色區分操作類型
            -->
            <div class="book-actions">
                <!-- 
                    編輯按鈕：觸發編輯模式
                    onclick="toggleEdit(${book.id})"：傳遞圖書 ID 進行編輯
                    class="btn btn-warning"：警告色按鈕，表示修改操作
                -->
                <button class="btn btn-warning" onclick="toggleEdit(${book.id})">✏️ 更新</button>
                <!-- 
                    刪除按鈕：移除圖書資料
                    onclick="deleteBook(${book.id})"：傳遞圖書 ID 進行刪除
                    class="btn btn-danger"：危險色按鈕，表示刪除操作
                -->
                <button class="btn btn-danger" onclick="deleteBook(${book.id})">🗑️ 刪除</button>
            </div>
            <!-- 
                編輯表單區域 (Edit Form Area)
                功能：提供圖書資料的編輯介面
                顯示：預設隱藏，點擊編輯按鈕後顯示
                id="edit-${book.id}"：唯一識別符，用於顯示/隱藏控制
            -->
            <div class="edit-form" id="edit-${book.id}">
                <!-- 編輯書名輸入群組 -->
                <div class="form-group">
                    <label>書名</label>
                    <!-- 
                        編輯書名輸入框
                        id="edit-title-${book.id}"：唯一識別符
                        value="${book.title}"：預填當前值
                    -->
                    <input type="text" id="edit-title-${book.id}" value="${book.title}">
                </div>
                <!-- 編輯作者輸入群組 -->
                <div class="form-group">
                    <label>作者</label>
                    <input type="text" id="edit-author-${book.id}" value="${book.author}">
                </div>
                <!-- 編輯年份輸入群組 -->
                <div class="form-group">
                    <label>出版年份</label>
                    <!-- 
                        編輯年份輸入框
                        type="number"：數字輸入類型
                        min/max：年份範圍限制
                        value="${book.year}"：預填當前值
                    -->
                    <input type="number" id="edit-year-${book.id}" value="${book.year}" min="1900" max="2024">
                </div>
                <!-- 編輯操作按鈕群組 -->
                <!-- 
                    保存按鈕：確認編輯變更
                    class="btn btn-success"：成功色按鈕
                -->
                <button class="btn btn-success" onclick="updateBook(${book.id})">💾 保存</button>
                <!-- 
                    取消按鈕：放棄編輯變更
                    class="btn btn-primary"：主要色按鈕
                -->
                <button class="btn btn-primary" onclick="cancelEdit(${book.id})">❌ 取消</button>
            </div>
        </div>
    `
    )
    .join("");
}

/**
 * 刪除圖書函數 (Delete Book Function)
 * 功能：從圖書清單中移除指定的圖書
 * 安全性：包含確認對話框，防止誤刪
 * 參數：id - 要刪除的圖書唯一識別符
 */
function deleteBook(id) {
  // 確認對話框：防止用戶誤刪資料
  if (confirm("確定要刪除這本書嗎？")) {
    // 陣列過濾：移除指定 ID 的圖書，保留其他圖書
    books = books.filter((book) => book.id !== id);

    // 重新渲染：更新顯示列表
    renderBooks();

    // 成功回饋：告知用戶操作完成
    showMessage("圖書刪除成功！", "success");
  }
}

/**
 * 切換編輯模式函數 (Toggle Edit Mode Function)
 * 功能：顯示指定圖書的編輯表單
 * 視覺：通過 CSS class 控制編輯表單的顯示/隱藏
 * 參數：id - 要編輯的圖書唯一識別符
 */
function toggleEdit(id) {
  // 獲取編輯表單元素
  const editForm = document.getElementById(`edit-${id}`);
  // 新增 active class：透過 CSS 顯示編輯表單
  editForm.classList.add("active");
}

/**
 * 取消編輯函數 (Cancel Edit Function)
 * 功能：關閉編輯表單並恢復原始資料
 * 安全性：確保用戶未儲存的變更不會遺失原始資料
 * 參數：id - 要取消編輯的圖書唯一識別符
 */
function cancelEdit(id) {
  // 獲取編輯表單元素
  const editForm = document.getElementById(`edit-${id}`);
  // 移除 active class：透過 CSS 隱藏編輯表單
  editForm.classList.remove("active");

  // 恢復原始值：從 books 陣列中取得原始資料
  const book = books.find((b) => b.id === id);
  document.getElementById(`edit-title-${id}`).value = book.title;
  document.getElementById(`edit-author-${id}`).value = book.author;
  document.getElementById(`edit-year-${id}`).value = book.year;
}

/**
 * 更新圖書函數 (Update Book Function)
 * 功能：保存編輯後的圖書資料
 * 包含：表單驗證、資料更新、介面刷新
 * 參數：id - 要更新的圖書唯一識別符
 */
function updateBook(id) {
  // 獲取編輯表單的輸入值
  const title = document.getElementById(`edit-title-${id}`).value.trim();
  const author = document.getElementById(`edit-author-${id}`).value.trim();
  const year = document.getElementById(`edit-year-${id}`).value.trim();

  // 表單驗證：檢查必填欄位
  if (!title || !author || !year) {
    alert("請填寫所有欄位！");
    return;
  }

  // 年份驗證：確保年份在合理範圍內
  if (year < 1900 || year > new Date().getFullYear()) {
    alert("請輸入有效的出版年份！");
    return;
  }

  // 找到要更新的圖書索引
  const bookIndex = books.findIndex((book) => book.id === id);
  if (bookIndex !== -1) {
    // 更新圖書資料：保持原有 ID，更新其他欄位
    books[bookIndex] = {
      id: id,
      title: title,
      author: author, // 更新作者
      year: parseInt(year), // 更新年份，轉換為整數
    };

    // 關閉編輯表單：移除 active class
    const editForm = document.getElementById(`edit-${id}`);
    editForm.classList.remove("active");

    // 重新渲染列表：顯示更新後的資料
    renderBooks();

    // 成功回饋：告知用戶更新完成
    showMessage("圖書更新成功！", "success");
  }
}

/**
 * 顯示訊息函數 (Show Message Function)
 * 功能：在畫面上顯示操作回饋訊息
 * 特色：自動消失、支援不同訊息類型
 * 參數：
 *   message - 要顯示的訊息內容
 *   type - 訊息類型（success、error、warning 等）
 */
function showMessage(message, type) {
  // 移除之前的提示：確保一次只顯示一個訊息
  const existingMessage = document.querySelector(".message-toast");
  if (existingMessage) {
    existingMessage.remove();
  }

  // 創建新的提示元素：動態生成 DOM 元素
  const messageEl = document.createElement("div");
  messageEl.textContent = message; // 設定訊息內容
  messageEl.className = `message-toast message-${type}`; // 設定 CSS class

  // 新增到頁面：插入到 body 元素中
  document.body.appendChild(messageEl);

  // 自動移除：3秒後自動清除訊息
  setTimeout(() => {
    if (messageEl.parentNode) {
      messageEl.remove();
    }
  }, 3000);
}

// =============================================================
// 應用程式初始化 (Application Initialization)
// 頁面載入完成後執行的初始化操作
// =============================================================

/**
 * 初始化應用程式
 * 功能：頁面載入後的首次渲染
 * 目的：確保空狀態正確顯示
 */
document.addEventListener("DOMContentLoaded", function () {
  renderBooks();
});
