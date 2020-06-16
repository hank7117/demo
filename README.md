
## 使用說明
- 請複製.env.example到專案根目錄，並更名為.env
- 請填入您的DB連線資訊
    - DB_CONNECTION=mysql
    - DB_HOST=127.0.0.1
    - DB_PORT=3306
    - DB_DATABASE=
    - DB_USERNAME=
    - DB_PASSWORD=
- 請填入您的FB資訊
    - FB_CLIENT_ID={fb client id}
    - FB_CLIENT_SECRET={fb client secret}
    - FB_REDIRECT={site domain}/callback
- 請至FB developer (https://developers.facebook.com/) -> 選擇應用程式 
-> 左側選單 -> 設定 -> 基本資料 -> 網站 -> 網站網址 -> 填入你的網站地址

## 登入及第三方登入
- 首頁右上提供login功能，該頁面同時整合facebook login

## 天氣資料
請執行php artisan insert:weather
新增資料至三個表
- countries (縣市，包含cid及名稱)
- weather (天氣，名稱, 圖片位置及早/晚)
- weatherforecasts (天氣詳細資料，包含日期,縣市,天氣,最低/高溫及早/晚)

