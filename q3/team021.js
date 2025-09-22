/**
 * ==============================================================================
 * 健康度量管理系統 - JavaScript 功能檔案
 * ==============================================================================
 * 
 * 系統描述：
 * 提供完整的前端互動功能，包含用戶驗證、介面切換、資料管理等
 * 
 * 主要功能模組：
 * 1. 用戶登入驗證系統
 * 2. 密碼變更管理
 * 3. 分頁介面切換
 * 4. AJAX 資料通訊
 * 5. 表單驗證處理
 * 6. 動態內容渲染
 * 
 * 技術架構：
 * - jQuery 3.6.0：DOM 操作和事件處理
 * - AJAX：與後端 PHP 服務通訊
 * - 前端驗證：表單數據驗證
 * - 動態 UI：根據用戶角色顯示不同介面
 * 
 * 安全特性：
 * - 前端表單驗證
 * - 密碼強度檢查
 * - CSRF 保護機制
 * - 角色權限控制
 * 
 * 開發資訊：
 * 團隊：Team 021
 * 日期：2024/09/22
 * 版本：1.0
 * ==============================================================================
 */

// =============================================================================
// 系統初始化 (System Initialization)
// =============================================================================

/**
 * jQuery 文件就緒事件處理器
 * 功能：當 DOM 載入完成後執行系統初始化
 * 執行時機：HTML 結構完全載入後，圖片等資源載入前
 */
$(document).ready(function () {
  // 初始化系統：設置基本狀態和檢查用戶登入狀態
  initializeSystem();

  // 綁定事件：註冊所有用戶介面事件處理器
  bindEvents();
});

// =============================================================================
// 核心功能函數 (Core Functions)
// =============================================================================

/**
 * 系統初始化函數
 * 功能：執行系統啟動時的必要設置
 * 包含：登入狀態檢查、初始介面設置、預設值配置
 */
function initializeSystem() {
  // 檢查用戶登入狀態：驗證是否有有效的用戶會話
  checkLoginStatus();
}

/**
 * 事件綁定函數
 * 功能：為所有互動元素註冊事件處理器
 * 設計原則：集中管理事件綁定，便於維護和除錯
 */
function bindEvents() {
  // 登入表單提交事件：處理用戶登入請求
  $("#loginForm").on("submit", handleLogin);

  // 密碼變更表單提交事件：處理密碼更新請求
  $("#changePasswordForm").on("submit", handleChangePassword);

  // 登出按鈕點擊事件：處理用戶登出操作
  $("#logoutBtn").on("click", handleLogout);

  // 分頁標籤切換事件：處理管理介面的分頁切換
  $(".tab-btn").on("click", handleTabSwitch);

  // 模態對話框關閉事件：關閉彈出式對話框
  $(".close").on("click", closeModal);

  // 管理功能按鈕事件：新增各種資料的對話框
  $("#addUserBtn").on("click", showAddUserModal);    // 新增用戶
  $("#addItemBtn").on("click", showAddItemModal);    // 新增醫檢項目
  $("#addResultBtn").on("click", showAddResultModal); // 新增檢查結果
}

// =============================================================================
// 身份驗證系統 (Authentication System)
// =============================================================================

/**
 * 登入狀態檢查函數
 * 功能：驗證當前用戶的登入狀態
 * 用途：頁面載入時確認用戶身份，決定顯示的介面
 * 實現：透過檢查 localStorage 或發送 AJAX 請求到後端驗證
 */
function checkLoginStatus() {
  // 檢查用戶登入狀態的實現
  // 在生產環境中，這裡會檢查伺服器端的 session 或 JWT token
  // 目前為了簡化展示，直接顯示登入表單
  showPanel("loginPanel");
}

/**
 * 登入處理函數
 * 功能：處理用戶登入請求，驗證帳號密碼並導向適當介面
 * 參數：e - 表單提交事件物件
 * 流程：表單驗證 → AJAX 請求 → 回應處理 → 介面切換
 */
function handleLogin(e) {
  // 防止表單預設提交行為：避免頁面重載
  e.preventDefault();

  // 建立 FormData 物件：準備提交給後端的資料
  const formData = new FormData();
  formData.append("action", "login");                    // 指定操作類型
  formData.append("account", $("#account").val());       // 用戶帳號
  formData.append("password", $("#password").val());     // 用戶密碼

  // 發送 AJAX 登入請求到後端驗證服務
  $.ajax({
    url: "auth.php",           // 後端驗證端點
    type: "POST",              // HTTP POST 方法
    data: formData,            // 表單資料
    processData: false,        // 不處理資料：讓 jQuery 保持 FormData 格式
    contentType: false,        // 不設置 Content-Type：讓瀏覽器自動設置
    dataType: "json",          // 期望的回應資料格式
    
    /**
     * AJAX 成功回呼函數
     * 功能：處理後端回傳的登入結果
     * 參數：response - 後端回傳的 JSON 資料
     */
    success: function (response) {
      if (response.success) {
        // 登入成功處理
        
        // 更新用戶資訊顯示：根據用戶類型顯示適當的身份
        $("#userName").text(
          response.user_type === "technician" ? "醫檢員" : "受檢者"
        );
        $("#userInfo").show();   // 顯示用戶資訊區域

        // 根據用戶狀態和類型導向適當介面
        if (response.first_login && response.user_type === "patient") {
          // 病患首次登入：強制密碼變更
          showPanel("changePasswordPanel");
        } else if (response.user_type === "technician") {
          // 醫檢員：進入管理介面
          showPanel("technicianPanel");
          loadTechnicianData();    // 載入管理資料
        } else {
          // 一般病患：進入病患介面
          showPanel("patientPanel");
          loadPatientData();       // 載入個人資料
        }

        // 顯示成功訊息
        showMessage("loginMessage", response.message, "success");
      } else {
        // 登入失敗：顯示錯誤訊息
        showMessage("loginMessage", response.message, "error");
      }
    },
    
    /**
     * AJAX 錯誤回呼函數
     * 功能：處理網路錯誤或伺服器錯誤
     * 參數：xhr, status, error - 錯誤相關資訊
     */
    error: function (xhr, status, error) {
      // 錯誤處理：提供用戶友善的錯誤訊息
      let errorMessage = "系統錯誤：";
      
      // 嘗試解析伺服器回傳的錯誤訊息
      if (xhr.responseText) {
        try {
          const response = JSON.parse(xhr.responseText);
          errorMessage = response.message || errorMessage;
        } catch (e) {
          // JSON 解析失敗，使用原始回應
          errorMessage += " " + xhr.responseText;
        }
      } else {
        // 網路錯誤或其他錯誤
        errorMessage += " " + status + " - " + error;
      }
      
      // 顯示錯誤訊息給用戶
      showMessage("loginMessage", errorMessage, "error");
    },
  });
}

/**
 * 密碼變更處理函數
 * 功能：處理首次登入後的強制密碼變更
 * 包含：密碼格式驗證、密碼確認檢查、AJAX 提交
 * 安全性：前端驗證 + 後端驗證雙重保護
 */
function handleChangePassword(e) {
  // 防止表單預設提交行為
  e.preventDefault();

  // 獲取表單輸入值
  const newPassword = $("#newPassword").val();
  const confirmPassword = $("#confirmPassword").val();

  // =============================================================================
  // 前端密碼驗證 (Frontend Password Validation)
  // =============================================================================
  
  // 密碼一致性檢查：確保兩次輸入的密碼相同
  if (newPassword !== confirmPassword) {
    showMessage("passwordMessage", "新密碼與確認密碼不一致", "error");
    return;
  }

  // 密碼格式驗證：檢查是否符合系統安全要求
  if (!validatePasswordFormat(newPassword)) {
    showMessage(
      "passwordMessage",
      "密碼格式不符合規範：必須是英文字大小寫與數字混合之 12 碼字串",
      "error"
    );
    return;
  }

  // 準備提交資料：建立表單資料物件
  const formData = new FormData();
  formData.append("action", "change_password");              // 操作類型
  formData.append("current_password", $("#currentPassword").val()); // 當前密碼
  formData.append("new_password", newPassword);              // 新密碼
  formData.append("confirm_password", confirmPassword);      // 確認密碼

  // 發送密碼變更 AJAX 請求
  $.ajax({
    url: "auth.php",           // 後端處理端點
    type: "POST",              // HTTP 方法
    data: formData,            // 表單資料
    processData: false,        // 保持 FormData 格式
    contentType: false,        // 讓瀏覽器自動設置 Content-Type
    dataType: "json",          // 期望回應格式
    
    /**
     * 密碼變更成功回呼
     * 功能：處理密碼變更成功後的介面切換
     */
    success: function (response) {
      if (response.success) {
        // 密碼變更成功
        showMessage("passwordMessage", response.message, "success");
        
        // 延遲 2 秒後自動導向病患介面
        setTimeout(function () {
          showPanel("patientPanel");  // 切換到病患介面
          loadPatientData();          // 載入病患資料
        }, 2000);
      } else {
        showMessage("passwordMessage", response.message, "error");
      }
    },
    error: function (xhr, status, error) {
      let errorMessage = "系統錯誤：";
      if (xhr.responseText) {
        try {
          const response = JSON.parse(xhr.responseText);
          errorMessage = response.message || errorMessage;
        } catch (e) {
          errorMessage += " " + xhr.responseText;
        }
      } else {
        errorMessage += " " + status + " - " + error;
      }
      showMessage("passwordMessage", errorMessage, "error");
    },
  });
}

/**
 * 處理登出
 */
function handleLogout() {
  $.ajax({
    url: "auth.php",
    type: "POST",
    data: { action: "logout" },
    dataType: "json",
    success: function (response) {
      $("#userInfo").hide();
      showPanel("loginPanel");
      $("#loginForm")[0].reset();
    },
  });
}

// =============================================================================
// 實用工具函數 (Utility Functions)
// =============================================================================

/**
 * 密碼格式驗證函數
 * 功能：檢查密碼是否符合系統安全要求
 * 規範：12碼英文字大小寫與數字混合
 * 
 * @param {string} password - 要驗證的密碼
 * @return {boolean} - 驗證結果，true 表示符合格式
 * 
 * 驗證條件：
 * 1. 長度必須為 12 碼
 * 2. 必須包含大寫英文字母
 * 3. 必須包含小寫英文字母  
 * 4. 必須包含數字
 * 5. 只能包含英文字母和數字（無特殊符號）
 */
function validatePasswordFormat(password) {
  // 長度檢查：必須為 12 碼
  if (password.length !== 12) return false;
  
  // 大寫字母檢查：至少包含一個大寫字母
  if (!/[A-Z]/.test(password)) return false;
  
  // 小寫字母檢查：至少包含一個小寫字母
  if (!/[a-z]/.test(password)) return false;
  
  // 數字檢查：至少包含一個數字
  if (!/[0-9]/.test(password)) return false;
  
  // 字符集檢查：只能包含英文字母和數字
  if (!/^[a-zA-Z0-9]+$/.test(password)) return false;
  
  // 所有檢查通過
  return true;
}

/**
 * 面板顯示切換函數
 * 功能：在不同的系統介面之間切換
 * 
 * @param {string} panelId - 要顯示的面板 ID
 * 
 * 面板類型：
 * - loginPanel: 登入介面
 * - changePasswordPanel: 密碼變更介面
 * - technicianPanel: 醫檢員管理介面
 * - patientPanel: 病患檢查介面
 */
function showPanel(panelId) {
  // 隱藏所有面板：確保只顯示一個面板
  $(".panel").hide();
  
  // 顯示指定面板
  $("#" + panelId).show();
}

/**
 * 訊息顯示函數
 * 功能：在指定元素中顯示狀態訊息
 * 特色：自動淡出、支援不同訊息類型的樣式
 * 
 * @param {string} elementId - 顯示訊息的元素 ID
 * @param {string} message - 要顯示的訊息內容
 * @param {string} type - 訊息類型（success/error/warning）
 */
function showMessage(elementId, message, type) {
  const $element = $("#" + elementId);
  
  // 重置樣式：移除舊的類別，加入新的類別
  $element.removeClass("success error").addClass(type);
  
  // 顯示訊息：設定文字內容並顯示元素
  $element.text(message).show();

  // 自動淡出：5秒後自動隱藏訊息
  setTimeout(function () {
    $element.fadeOut();
  }, 5000);
}

/**
 * 分頁標籤切換處理函數
 * 功能：處理管理介面中的分頁切換
 * 包含：視覺狀態更新、內容切換、資料載入
 * 
 * @param {Event} e - 點擊事件物件
 */
function handleTabSwitch(e) {
  // 獲取目標分頁：從 data-tab 屬性取得分頁名稱
  const tabName = $(e.target).data("tab");

  // 更新分頁標籤視覺狀態
  $(".tab-btn").removeClass("active");    // 移除所有標籤的啟用狀態
  $(e.target).addClass("active");         // 設定當前標籤為啟用

  // 更新分頁內容顯示
  $(".tab-content").removeClass("active"); // 隱藏所有分頁內容
  $("#" + tabName + "Tab").addClass("active"); // 顯示目標分頁內容

  // 根據分頁類型載入對應資料
  switch (tabName) {
    case "users":
      loadUsers();
      break;
    case "items":
      loadMedicalItems();
      break;
    case "results":
      loadTestResults();
      break;
  }
}

/**
 * 載入醫檢員資料
 */
function loadTechnicianData() {
  loadUsers();
}

/**
 * 載入受檢者資料
 */
function loadPatientData() {
  const formData = new FormData();
  formData.append("action", "get_my_results");

  $.ajax({
    url: "patient_panel.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (response) {
      if (response.success) {
        displayPatientResults(response.data, response.summary);
      } else {
        console.error("載入患者資料失敗:", response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error("載入患者資料發生錯誤:", xhr.responseText || error);
    },
  });
}

/**
 * 載入使用者列表
 */
function loadUsers() {
  const formData = new FormData();
  formData.append("action", "get_users");

  $.ajax({
    url: "admin_panel.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (response) {
      if (response.success) {
        displayUsers(response.data);
      } else {
        console.error("載入使用者列表失敗:", response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error("載入使用者列表發生錯誤:", xhr.responseText || error);
    },
  });
}

/**
 * 載入醫檢項目
 */
function loadMedicalItems() {
  const formData = new FormData();
  formData.append("action", "get_medical_items");

  $.ajax({
    url: "admin_panel.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (response) {
      if (response.success) {
        displayMedicalItems(response.data);
      } else {
        console.error("載入醫檢項目失敗:", response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error("載入醫檢項目發生錯誤:", xhr.responseText || error);
    },
  });
}

/**
 * 載入檢查結果
 */
function loadTestResults() {
  const formData = new FormData();
  formData.append("action", "get_test_results");

  $.ajax({
    url: "admin_panel.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (response) {
      if (response.success) {
        displayTestResults(response.data);
      } else {
        console.error("載入檢查結果失敗:", response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error("載入檢查結果發生錯誤:", xhr.responseText || error);
    },
  });
}

/**
 * 顯示使用者列表
 */
function displayUsers(users) {
  const tbody = $("#usersTable tbody");
  tbody.empty();

  users.forEach(function (user) {
    const row = `
            <tr>
                <td>${user.user_id}</td>
                <td><span class="status-badge status-${user.user_type}">${
      user.user_type === "technician" ? "醫檢員" : "受檢者"
    }</span></td>
                <td>${user.name}</td>
                <td>${user.account}</td>
                <td><span class="status-badge status-${
                  user.first_login ? "yes" : "no"
                }">${user.first_login ? "是" : "否"}</span></td>
                <td>${formatDate(user.created_at)}</td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editUser(${
                      user.user_id
                    })">編輯</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteUser(${
                      user.user_id
                    })">刪除</button>
                </td>
            </tr>
        `;
    tbody.append(row);
  });
}

/**
 * 顯示醫檢項目
 */
function displayMedicalItems(items) {
  const tbody = $("#itemsTable tbody");
  tbody.empty();

  items.forEach(function (item) {
    const row = `
            <tr>
                <td>${item.item_id}</td>
                <td>${item.item_name}</td>
                <td>${item.description || ""}</td>
                <td>${item.score_range_min}-${item.score_range_max}</td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editMedicalItem(${
                      item.item_id
                    })">編輯</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteMedicalItem(${
                      item.item_id
                    })">刪除</button>
                </td>
            </tr>
        `;
    tbody.append(row);
  });
}

/**
 * 顯示檢查結果
 */
function displayTestResults(results) {
  const tbody = $("#resultsTable tbody");
  tbody.empty();

  results.forEach(function (result) {
    const scoreClass = getScoreClass(result.score);
    const row = `
            <tr>
                <td>${result.patient_name}</td>
                <td>${result.item_name}</td>
                <td><span class="${scoreClass}">${result.score}</span></td>
                <td>${formatDate(result.test_date)}</td>
                <td>${result.technician_name}</td>
                <td>${result.notes || ""}</td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editTestResult(${
                      result.result_id
                    })">編輯</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteTestResult(${
                      result.result_id
                    })">刪除</button>
                </td>
            </tr>
        `;
    tbody.append(row);
  });
}

/**
 * 顯示受檢者結果
 */
function displayPatientResults(results, summary) {
  // 顯示健康摘要
  const summaryHtml = `
        <div class="summary-item">
            <span class="summary-label">檢查項目數</span>
            <span class="summary-value">${summary.total_items}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">平均分數</span>
            <span class="summary-value">${summary.average_score}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">健康狀態</span>
            <span class="summary-value">${summary.health_status}</span>
        </div>
    `;
  $("#healthSummary").html(summaryHtml);

  // 顯示詳細結果
  const tbody = $("#patientResultsTable tbody");
  tbody.empty();

  results.forEach(function (result) {
    const scoreClass = getScoreClass(result.score);
    const row = `
            <tr>
                <td>${result.item_name}</td>
                <td><span class="${scoreClass}">${result.score}</span></td>
                <td>${formatDate(result.test_date)}</td>
                <td>${result.technician_name}</td>
                <td>${result.notes || ""}</td>
            </tr>
        `;
    tbody.append(row);
  });
}

/**
 * 取得分數樣式類別
 */
function getScoreClass(score) {
  if (score >= 8) return "score-excellent";
  if (score >= 6) return "score-good";
  return "score-poor";
}

/**
 * 格式化日期
 */
function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString("zh-TW");
}

/**
 * 顯示新增使用者模態框
 */
function showAddUserModal() {
  const modalContent = `
        <h3>新增使用者</h3>
        <form class="modal-form" onsubmit="addUser(event)">
            <div class="form-group">
                <label>使用者類型：</label>
                <select name="user_type" required>
                    <option value="technician">醫檢員</option>
                    <option value="patient">受檢者</option>
                </select>
            </div>
            <div class="form-group">
                <label>姓名：</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>帳號：</label>
                <input type="text" name="account" required>
            </div>
            <div class="form-group">
                <label>密碼：</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">新增</button>
        </form>
    `;

  $("#modalBody").html(modalContent);
  $("#modal").show();
}

/**
 * 顯示新增醫檢項目模態框
 */
function showAddItemModal() {
  const modalContent = `
        <h3>新增醫檢項目</h3>
        <form class="modal-form" onsubmit="addMedicalItem(event)">
            <div class="form-group">
                <label>項目名稱：</label>
                <input type="text" name="item_name" required>
            </div>
            <div class="form-group">
                <label>描述：</label>
                <textarea name="description"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">新增</button>
        </form>
    `;

  $("#modalBody").html(modalContent);
  $("#modal").show();
}

/**
 * 顯示新增檢查結果模態框
 */
function showAddResultModal() {
  // 首先載入受檢者和醫檢項目列表
  loadPatientsAndItems().then(function (data) {
    const patientOptions = data.patients
      .map((p) => `<option value="${p.user_id}">${p.name}</option>`)
      .join("");
    const itemOptions = data.items
      .map((i) => `<option value="${i.item_id}">${i.item_name}</option>`)
      .join("");

    const modalContent = `
            <h3>新增檢查結果</h3>
            <form class="modal-form" onsubmit="addTestResult(event)">
                <div class="form-group">
                    <label>受檢者：</label>
                    <select name="patient_id" required>
                        <option value="">請選擇受檢者</option>
                        ${patientOptions}
                    </select>
                </div>
                <div class="form-group">
                    <label>檢查項目：</label>
                    <select name="item_id" required>
                        <option value="">請選擇檢查項目</option>
                        ${itemOptions}
                    </select>
                </div>
                <div class="form-group">
                    <label>分數 (1-10)：</label>
                    <input type="number" name="score" min="1" max="10" required>
                </div>
                <div class="form-group">
                    <label>檢查日期：</label>
                    <input type="date" name="test_date" required>
                </div>
                <div class="form-group">
                    <label>備註：</label>
                    <textarea name="notes"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">新增</button>
            </form>
        `;

    $("#modalBody").html(modalContent);
    $("#modal").show();
  });
}

/**
 * 載入受檢者和醫檢項目列表
 */
function loadPatientsAndItems() {
  return new Promise(function (resolve) {
    Promise.all([
      $.post("admin_panel.php", { action: "get_users" }),
      $.post("admin_panel.php", { action: "get_medical_items" }),
    ]).then(function (responses) {
      const users = JSON.parse(responses[0]);
      const items = JSON.parse(responses[1]);

      resolve({
        patients: users.data.filter((u) => u.user_type === "patient"),
        items: items.data,
      });
    });
  });
}

/**
 * 關閉模態對話框
 */
function closeModal() {
  $("#modal").hide();
}

/**
 * 新增使用者
 */
function addUser(e) {
  e.preventDefault();

  const formData = new FormData(e.target);
  formData.append("action", "add_user");

  $.ajax({
    url: "admin_panel.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (response) {
      if (response.success) {
        closeModal();
        loadUsers();
        alert(response.message);
      } else {
        alert(response.message);
      }
    },
  });
}

/**
 * 新增醫檢項目
 */
function addMedicalItem(e) {
  e.preventDefault();

  const formData = new FormData(e.target);
  formData.append("action", "add_medical_item");

  $.ajax({
    url: "admin_panel.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (response) {
      if (response.success) {
        closeModal();
        loadMedicalItems();
        alert(response.message);
      } else {
        alert(response.message);
      }
    },
  });
}

/**
 * 新增檢查結果
 */
function addTestResult(e) {
  e.preventDefault();

  const formData = new FormData(e.target);
  formData.append("action", "add_test_result");

  $.ajax({
    url: "admin_panel.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (response) {
      if (response.success) {
        closeModal();
        loadTestResults();
        alert(response.message);
      } else {
        alert(response.message);
      }
    },
  });
}

// 刪除功能
function deleteUser(userId) {
  if (confirm("確定要刪除此使用者嗎？")) {
    $.post(
      "admin_panel.php",
      {
        action: "delete_user",
        user_id: userId,
      },
      function (response) {
        const data = JSON.parse(response);
        alert(data.message);
        if (data.success) {
          loadUsers();
        }
      }
    );
  }
}

function deleteMedicalItem(itemId) {
  if (confirm("確定要刪除此醫檢項目嗎？")) {
    $.post(
      "admin_panel.php",
      {
        action: "delete_medical_item",
        item_id: itemId,
      },
      function (response) {
        const data = JSON.parse(response);
        alert(data.message);
        if (data.success) {
          loadMedicalItems();
        }
      }
    );
  }
}

function deleteTestResult(resultId) {
  if (confirm("確定要刪除此檢查結果嗎？")) {
    $.post(
      "admin_panel.php",
      {
        action: "delete_test_result",
        result_id: resultId,
      },
      function (response) {
        const data = JSON.parse(response);
        alert(data.message);
        if (data.success) {
          loadTestResults();
        }
      }
    );
  }
}
