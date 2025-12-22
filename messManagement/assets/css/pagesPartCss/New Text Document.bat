@echo off
set /p ext=Enter extension (.css or .js): 
if /i "%ext%"==".css" (
    echo /* applications.css */ > applications.css
    echo /* bills.css */ > bills.css
    echo /* dashboard.css */ > dashboard.css
    echo /* hostel.css */ > hostel.css
    echo /* meals.css */ > meals.css
    echo /* members.css */ > members.css
    echo /* notices.css */ > notices.css
    echo /* payments.css */ > payments.css
    echo /* rooms.css */ > rooms.css
    echo /* seat_ads.css */ > seat_ads.css
    echo CSS files created successfully!
) else if /i "%ext%"==".js" (
    echo // applications.js > applications.js
    echo // bills.js > bills.js
    echo // dashboard.js > dashboard.js
    echo // hostel.js > hostel.js
    echo // meals.js > meals.js
    echo // members.js > members.js
    echo // notices.js > notices.js
    echo // payments.js > payments.js
    echo // rooms.js > rooms.js
    echo // seat_ads.js > seat_ads.js
    echo JS files created successfully!
) else (
    echo Invalid input! Please enter .css or .js
)
pause