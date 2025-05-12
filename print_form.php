<?php session_start();

// Проверка авторизации
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    // Если пользователь не авторизован, перенаправляем на страницу входа
    header('Location: index.php');
    exit;
}
 ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Рецепт для печати</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Times New Roman, Times, serif;
            font-size: 13px;
        }
        .main-flex {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            justify-content: center;
            min-height: 100vh;
        }
        .preview {
            flex: 1 1 70%;
            max-width: 900px;
        }
        .controls {
            flex: 0 0 220px;
            margin-left: 32px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            align-items: flex-start;
            position: sticky;
            top: 30px;
            right: 0;
            z-index: 10;
        }
        .controls button {
            padding: 10px 24px;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #888;
            background: #f5f5f5;
            cursor: pointer;
            transition: background 0.2s;
        }
        .controls button:hover {
            background: #e0e0e0;
        }
        @media print {
            .controls, .no-print { display: none !important; }
            .main-flex, .preview { display: block !important; width: 100% !important; margin: 0 !important; }
            body { margin: 0; }
        }
        .print-sheet {
    width: 210mm;
    height: 297mm;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: 1fr 1fr;
    gap: 0;
    page-break-after: always;
    position: relative;
}
.recept-block {
    border: none;
    padding: 7mm 7mm 0 7mm;
    box-sizing: border-box;
    height: 148mm;
    width: 100%;
    position: relative;
    /* Границы разреза */
    border-right: 2px dashed #888;
    border-bottom: 2px dashed #888;
}
.print-sheet .recept-block:nth-child(2),
.print-sheet .recept-block:nth-child(4) {
    border-right: none;
}
.print-sheet .recept-block:nth-child(3),
.print-sheet .recept-block:nth-child(4) {
    border-bottom: none;
}
        .header {
            text-align: center;
            font-size: 11px;
            margin-bottom: 2mm;
        }
        .header .org {
            font-weight: bold;
            font-size: 13px;
        }
        .header .small {
            font-size: 10px;
        }
        .recept-title {
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            margin: 2mm 0 2mm 0;
        }
        .row {
            text-align:center;
        }
        .date-line {
            border-bottom: 1px solid #000;
            width: 30mm;
            display: inline-block;
            margin: 0 2mm;
        }
        .field-label {
            margin-bottom: 1mm;
        }
        .line {
            border-bottom: 1px solid #000;
            width: 100%;
            display: inline-block;
            height: 2mm;
        }
        .sign-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 3mm;
        }
        .sign-label {
            font-size: 11px;
        }
        .footer {
            font-size: 10px;
            margin-top: 2mm;
            text-align: left;
        }
        .footer .underline {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 30mm;
            height: 2mm;
            vertical-align: bottom;
        }
        .print-value {
            display: none;
            border-bottom: 1px solid #000;
            min-height: 18px;
            padding: 0 2px;
            font-family: inherit;
            font-size: 18px;
            background: none;
            white-space: normal;
            word-break: break-word;
        }
        .input-line {
            width: 100%;
            border: none;
            border-bottom: 1px solid #000;
            font-family: inherit;
            font-size: 18px;
            background: none;
            outline: none;
            padding: 0 2px;
            white-space: normal;
            word-break: break-word;
        }
		
        .input-line.short {
            width: 30mm;
            display: inline-block;
        }
        .input-line.rp, .print-value.rp {
            font-size: 16px;
        }
        .input-line.rp {
            width: 92%;
            display: inline-block;
        }
		.input-line.rp1 {
            width: 100%;
            display: inline-block;
        }
        textarea.input-line {
            min-height: 17px;
            resize: none;
        }
    </style>
</head>
<body>
    <div class="main-flex">
        <div class="preview">
            <div class="print-sheet">
                <!-- Один блок с интерактивными полями -->
                <div class="recept-block">
                    <div class="header">
                        <div style="float:left; text-align:left; width:50%;font-size:18px;">
                           
							<br>
                        </div>
                        <div style="float:right; text-align:right; width:50%;">
                            Код формы по ОКУД<br>
                            Код учреждения по ОКПО<br>
                            Медицинская документация<br>
                            Форма № 107-1/у<br>
                            <span class="small">Утверждена приказом<br>Министерства здравоохранения<br>Российской Федерации<br>от 14 января 2019 г. № 4н</span>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="recept-title">РЕЦЕПТ</div>
                    <div class="row">
                        <span>«<input type="text" name="date_day" maxlength="2" class="input-line short" style="width:18px;"> <span class="print-value short" id="pv_date_day"></span>»</span>
                        <span><input type="text" name="date_month" maxlength="2" class="input-line short" style="text-align:center"> <span class="print-value short" id="pv_date_month"></span></span>
                        <span><input type="text" name="date_year" maxlength="4" class="input-line short" style="width:38px;"> <span class="print-value short" id="pv_date_year"></span>г.</span>
                    </div>
                    <div class="field-label">Фамилия, инициалы пациента, дата рождения</div>
                    <input type="text" name="fio_patient" class="input-line">
                    <span class="print-value" id="pv_fio_patient"></span>
                    <div class="field-label">Фамилия, инициалы лечащего врача (фельдшера, акушера)</div>
                    <input type="text" name="fio_doctor" class="input-line" value="<?php echo isset($_SESSION['user']['fio_short']) ? htmlspecialchars($_SESSION['user']['fio_short']) : '' ?>">
                    <span class="print-value" id="pv_fio_doctor"></span>
                    <div class="rp">Rp. <input type="text" name="rp1" class="input-line rp"><span class="print-value" id="pv_rp1"></span></div>
                    <div class="rp"><input type="text" name="rp2" class="input-line rp1"><span class="print-value" id="pv_rp2"></span></div>
                    <div class="rp"><input type="text" name="rp3" class="input-line rp1"><span class="print-value" id="pv_rp3"></span></div>
                    <div class="rp">Rp. <input type="text" name="rp4" class="input-line rp"><span class="print-value" id="pv_rp4"></span></div>
					<div class="rp"><input type="text" name="rp5" class="input-line rp1"><span class="print-value" id="pv_rp5"></span></div>
                    <div class="rp"><input type="text" name="rp6" class="input-line rp1"><span class="print-value" id="pv_rp6"></span></div>
					<div class="rp">Rp. <input type="text" name="rp7" class="input-line rp"><span class="print-value" id="pv_rp7"></span></div>
                    <div class="rp"><input type="text" name="rp8" class="input-line rp1"><span class="print-value" id="pv_rp8"></span></div>
                    <div class="rp"><input type="text" name="rp9" class="input-line rp1"><span class="print-value" id="pv_rp9"></span></div>
                    <div class="sign-row">
                        <div>
                            Подпись<br>
                            и печать лечащего врача<br>
                            <span class="sign-label">(подпись фельдшера, акушера)</span>
                        </div>
                        <div>М.П.</div>
                    </div>
                    <div class="footer">
                        Рецепт действителен в течение <b>60 дней</b>, до 1 года (<input type="text" name="months" class="input-line short"><span class="print-value short" id="pv_months"></span>)<br>
                    </div>
                </div>
                <!-- 2 блок с интерактивными полями -->
                <div class="recept-block">
                    <div class="header">
                        <div style="float:left; text-align:left; width:50%;font-size:18px;">
                           
							<br>
                        </div>
                        <div style="float:right; text-align:right; width:50%;">
                            Код формы по ОКУД<br>
                            Код учреждения по ОКПО<br>
                            Медицинская документация<br>
                            Форма № 107-1/у<br>
                            <span class="small">Утверждена приказом<br>Министерства здравоохранения<br>Российской Федерации<br>от 14 января 2019 г. № 4н</span>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="recept-title">РЕЦЕПТ</div>
                    <div class="row">
                        <span>«<input type="text" name="date_day" maxlength="2" class="input-line short" style="width:18px;"> <span class="print-value short" id="pv_date_day"></span>»</span>
                        <span><input type="text" name="date_month" maxlength="2" class="input-line short" style="text-align:center"> <span class="print-value short" id="pv_date_month"></span></span>
                        <span><input type="text" name="date_year" maxlength="4" class="input-line short" style="width:38px;"> <span class="print-value short" id="pv_date_year"></span>г.</span>
                    </div>
                    <div class="field-label">Фамилия, инициалы пациента, дата рождения</div>
                    <input type="text" name="fio_patient" class="input-line">
                    <span class="print-value" id="pv_fio_patient"></span>
                    <div class="field-label">Фамилия, инициалы лечащего врача (фельдшера, акушера)</div>
                    <input type="text" name="fio_doctor" class="input-line" value="<?php echo isset($_SESSION['user']['fio_short']) ? htmlspecialchars($_SESSION['user']['fio_short']) : '' ?>">
                    <span class="print-value" id="pv_fio_doctor"></span>
                    <div class="rp">Rp. <input type="text" name="rp1" class="input-line rp"><span class="print-value" id="pv_rp1"></span></div>
                    <div class="rp"><input type="text" name="rp2" class="input-line rp1"><span class="print-value" id="pv_rp2"></span></div>
                    <div class="rp"><input type="text" name="rp3" class="input-line rp1"><span class="print-value" id="pv_rp3"></span></div>
                    <div class="rp">Rp. <input type="text" name="rp4" class="input-line rp"><span class="print-value" id="pv_rp4"></span></div>
					<div class="rp"><input type="text" name="rp5" class="input-line rp1"><span class="print-value" id="pv_rp5"></span></div>
                    <div class="rp"><input type="text" name="rp6" class="input-line rp1"><span class="print-value" id="pv_rp6"></span></div>
					<div class="rp">Rp. <input type="text" name="rp7" class="input-line rp"><span class="print-value" id="pv_rp7"></span></div>
                    <div class="rp"><input type="text" name="rp8" class="input-line rp1"><span class="print-value" id="pv_rp8"></span></div>
                    <div class="rp"><input type="text" name="rp9" class="input-line rp1"><span class="print-value" id="pv_rp9"></span></div>
                    <div class="sign-row">
                        <div>
                            Подпись<br>
                            и печать лечащего врача<br>
                            <span class="sign-label">(подпись фельдшера, акушера)</span>
                        </div>
                        <div>М.П.</div>
                    </div>
                    <div class="footer">
                        Рецепт действителен в течение <b>60 дней</b>, до 1 года (<input type="text" name="months" class="input-line short"><span class="print-value short" id="pv_months"></span>)<br>
                    </div>
                </div>
				<!-- 3 блок с интерактивными полями -->
                <div class="recept-block">
                    <div class="header">
                        <div style="float:left; text-align:left; width:50%;font-size:18px;">
                           
							<br>
                        </div>
                        <div style="float:right; text-align:right; width:50%;">
                            Код формы по ОКУД<br>
                            Код учреждения по ОКПО<br>
                            Медицинская документация<br>
                            Форма № 107-1/у<br>
                            <span class="small">Утверждена приказом<br>Министерства здравоохранения<br>Российской Федерации<br>от 14 января 2019 г. № 4н</span>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="recept-title">РЕЦЕПТ</div>
                    <div class="row">
                        <span>«<input type="text" name="date_day" maxlength="2" class="input-line short" style="width:18px;"> <span class="print-value short" id="pv_date_day"></span>»</span>
                        <span><input type="text" name="date_month" maxlength="2" class="input-line short" style="text-align:center"> <span class="print-value short" id="pv_date_month"></span></span>
                        <span><input type="text" name="date_year" maxlength="4" class="input-line short" style="width:38px;"> <span class="print-value short" id="pv_date_year"></span>г.</span>
                    </div>
                    <div class="field-label">Фамилия, инициалы пациента, дата рождения</div>
                    <input type="text" name="fio_patient" class="input-line">
                    <span class="print-value" id="pv_fio_patient"></span>
                    <div class="field-label">Фамилия, инициалы лечащего врача (фельдшера, акушера)</div>
                    <input type="text" name="fio_doctor" class="input-line" value="<?php echo isset($_SESSION['user']['fio_short']) ? htmlspecialchars($_SESSION['user']['fio_short']) : '' ?>">
                    <span class="print-value" id="pv_fio_doctor"></span>
                    <div class="rp">Rp. <input type="text" name="rp1" class="input-line rp"><span class="print-value" id="pv_rp1"></span></div>
                    <div class="rp"><input type="text" name="rp2" class="input-line rp1"><span class="print-value" id="pv_rp2"></span></div>
                    <div class="rp"><input type="text" name="rp3" class="input-line rp1"><span class="print-value" id="pv_rp3"></span></div>
                    <div class="rp">Rp. <input type="text" name="rp4" class="input-line rp"><span class="print-value" id="pv_rp4"></span></div>
					<div class="rp"><input type="text" name="rp5" class="input-line rp1"><span class="print-value" id="pv_rp5"></span></div>
                    <div class="rp"><input type="text" name="rp6" class="input-line rp1"><span class="print-value" id="pv_rp6"></span></div>
					<div class="rp">Rp. <input type="text" name="rp7" class="input-line rp"><span class="print-value" id="pv_rp7"></span></div>
                    <div class="rp"><input type="text" name="rp8" class="input-line rp1"><span class="print-value" id="pv_rp8"></span></div>
                    <div class="rp"><input type="text" name="rp9" class="input-line rp1"><span class="print-value" id="pv_rp9"></span></div>
                    <div class="sign-row">
                        <div>
                            Подпись<br>
                            и печать лечащего врача<br>
                            <span class="sign-label">(подпись фельдшера, акушера)</span>
                        </div>
                        <div>М.П.</div>
                    </div>
                    <div class="footer">
                        Рецепт действителен в течение <b>60 дней</b>, до 1 года (<input type="text" name="months" class="input-line short"><span class="print-value short" id="pv_months"></span>)<br>
                    </div>
                </div>
				<!-- 4 блок с интерактивными полями -->
                <div class="recept-block">
                    <div class="header">
                        <div style="float:left; text-align:left; width:50%;font-size:18px;">
                            
							<br>
                        </div>
                        <div style="float:right; text-align:right; width:50%;">
                            Код формы по ОКУД<br>
                            Код учреждения по ОКПО<br>
                            Медицинская документация<br>
                            Форма № 107-1/у<br>
                            <span class="small">Утверждена приказом<br>Министерства здравоохранения<br>Российской Федерации<br>от 14 января 2019 г. № 4н</span>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="recept-title">РЕЦЕПТ</div>
                    <div class="row">
                        <span>«<input type="text" name="date_day" maxlength="2" class="input-line short" style="width:18px;"> <span class="print-value short" id="pv_date_day"></span>»</span>
                        <span><input type="text" name="date_month" maxlength="2" class="input-line short" style="text-align:center"> <span class="print-value short" id="pv_date_month"></span></span>
                        <span><input type="text" name="date_year" maxlength="4" class="input-line short" style="width:38px;"> <span class="print-value short" id="pv_date_year"></span>г.</span>
                    </div>
                    <div class="field-label">Фамилия, инициалы пациента, дата рождения</div>
                    <input type="text" name="fio_patient" class="input-line">
                    <span class="print-value" id="pv_fio_patient"></span>
                    <div class="field-label">Фамилия, инициалы лечащего врача (фельдшера, акушера)</div>
                    <input type="text" name="fio_doctor" class="input-line" value="<?php echo isset($_SESSION['user']['fio_short']) ? htmlspecialchars($_SESSION['user']['fio_short']) : '' ?>">
                    <span class="print-value" id="pv_fio_doctor"></span>
                    <div class="rp">Rp. <input type="text" name="rp1" class="input-line rp"><span class="print-value" id="pv_rp1"></span></div>
                    <div class="rp"><input type="text" name="rp2" class="input-line rp1"><span class="print-value" id="pv_rp2"></span></div>
                    <div class="rp"><input type="text" name="rp3" class="input-line rp1"><span class="print-value" id="pv_rp3"></span></div>
                    <div class="rp">Rp. <input type="text" name="rp4" class="input-line rp"><span class="print-value" id="pv_rp4"></span></div>
					<div class="rp"><input type="text" name="rp5" class="input-line rp1"><span class="print-value" id="pv_rp5"></span></div>
                    <div class="rp"><input type="text" name="rp6" class="input-line rp1"><span class="print-value" id="pv_rp6"></span></div>
					<div class="rp">Rp. <input type="text" name="rp7" class="input-line rp"><span class="print-value" id="pv_rp7"></span></div>
                    <div class="rp"><input type="text" name="rp8" class="input-line rp1"><span class="print-value" id="pv_rp8"></span></div>
                    <div class="rp"><input type="text" name="rp9" class="input-line rp1"><span class="print-value" id="pv_rp9"></span></div>
                    <div class="sign-row">
                        <div>
                            Подпись<br>
                            и печать лечащего врача<br>
                            <span class="sign-label">(подпись фельдшера, акушера)</span>
                        </div>
                        <div>М.П.</div>
                    </div>
                    <div class="footer">
                        Рецепт действителен в течение <b>60 дней</b>, до 1 года (<input type="text" name="months" class="input-line short"><span class="print-value short" id="pv_months"></span>)<br>
                    </div>
                </div>
            </div>
        </div>
        <div class="controls">
            <button onclick="window.print()">Печать</button>
            <button onclick="window.location.href='patient_prescription.php'">Назад</button>
        </div>
    </div>

    <script>
        // Функция для преобразования ФИО в формат Фамилия И.О.
        function fioToInitials(fio) {
            if (!fio) return '';
            const parts = fio.trim().split(' ');
            if (parts.length < 2) return fio;
            const last = parts[0];
            const first = parts[1] ? parts[1][0].toUpperCase() + '.' : '';
            const patr = parts[2] ? parts[2][0].toUpperCase() + '.' : '';
            return `${last} ${first}${patr}`;
        }

        // Обновляем функцию splitDrugText для более гибкого переноса
        function splitDrugText(drug, maxLen) {
            if (!drug) return ['', ''];
            if (drug.length <= maxLen) return [drug, ''];
            
            // Ищем последний пробел до maxLen
            let idx = drug.lastIndexOf(' ', maxLen);
            if (idx === -1) idx = maxLen; // если нет пробела, режем по maxLen
            
            return [drug.slice(0, idx), drug.slice(idx).trim()];
        }

        // Добавим новую функцию для расчета размера шрифта
        function calculateFontSize(text, maxWidth) {
            const baseSize = 18; // Базовый размер шрифта
            const minSize = 12;  // Минимальный размер шрифта
            const maxChars = 40; // Максимальное количество символов для базового размера
            
            if (!text) return baseSize;
            
            // Если текст длиннее максимального количества символов, уменьшаем размер
            if (text.length > maxChars) {
                const ratio = maxChars / text.length;
                const newSize = Math.floor(baseSize * ratio);
                return Math.max(newSize, minSize);
            }
            
            return baseSize;
        }

        // Обновляем функцию initializeData
        function initializeData() {
            try {
                const printData = JSON.parse(sessionStorage.getItem('printData'));
                if (!printData) {
                    alert('Данные не найдены');
                    window.location.href = 'patient_prescription.php';
                    return;
                }

                const today = new Date();
                const day = today.getDate().toString().padStart(2, '0');
                const month = (today.getMonth() + 1).toString().padStart(2, '0');
                const year = today.getFullYear();
                const patientNumber = printData.patient.number || '';
                const fioShort = fioToInitials(printData.patient.fio);
                const fioBirth = ' ' + fioShort + ',   ' + printData.patient.birth_date;

                document.querySelectorAll('.recept-block').forEach(function(block, idx) {
                    // Номер пациента и статус под названием организации (слева)
                    const orgDiv = block.querySelector('.header > div[style*="float:left"]');
                    if (orgDiv) {
                        // Удаляем старые данные, если они есть
                        orgDiv.querySelectorAll('b, .sk-code, .ptrang-name').forEach(el => el.remove());

                        // Добавляем новые данные
                        orgDiv.innerHTML += `<br><b>${patientNumber}</b>`;
                        if (printData.patient.ptpart_code) {
                            orgDiv.innerHTML += `<br><span class="sk-code" style="font-size:15px;">СК-${printData.patient.ptpart_code}</span>`;
                        }
                        // Проверяем наличие звания и убираем "null" если оно есть
                        let ptrangName = printData.patient.ptrang_name || '';
                        ptrangName = ptrangName.replace(/^null\s*/i, '').trim();
                        if (!ptrangName) ptrangName = ' ';
                        orgDiv.innerHTML += `<br><span class="ptrang-name" style="font-size:15px;">${ptrangName}</span>`;
                    }

                    // Дата
                    const monthsRu = [
  'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
  'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'
];

// Преобразуем строковое значение месяца в индекс (минус 1)
const monthIndex = parseInt(month, 10) - 1;
const monthNameRu = monthsRu[monthIndex] || '';

// Дата
const dateDay = block.querySelector('input[name="date_day"]');
const dateMonth = block.querySelector('input[name="date_month"]');
const dateYear = block.querySelector('input[name="date_year"]');

if (dateDay) dateDay.value = day;
if (dateMonth) dateMonth.value = monthNameRu; // Теперь здесь будет название месяца словом
if (dateYear) dateYear.value = year;

const dateLines = block.querySelectorAll('.date-line');
if (dateLines.length) {
    if (dateLines[0]) dateLines[0].textContent = day;
    if (dateLines[1]) dateLines[1].textContent = monthNameRu; // Месяц словом
    if (dateLines[2]) dateLines[2].textContent = year;
}

                    // Фамилия, инициалы и дата рождения пациента одной строкой
                    const fioInput = block.querySelector('input[name="fio_patient"]');
                    if (fioInput) fioInput.value = fioBirth;
                    const fioSpan = block.querySelector('#pv_fio_patient');
                    if (fioSpan) fioSpan.textContent = fioBirth;
                    const lines = block.querySelectorAll('.line');
                    if (lines.length > 0) lines[0].textContent = fioBirth;
                    // Заполнение Rp по правилам: 3 лекарства на блок, каждое на 2 строки (6 строк)
                    const drugInputs = block.querySelectorAll('input[name^="rp"]');
                    drugInputs.forEach(input => input.value = ''); // очистка

                    // Заполняем 3 лекарства
                    for (let i = 0; i < 3; i++) {
                        const drugIdx = idx * 3 + i;
                        if (printData.drugs && printData.drugs[drugIdx]) {
                            const drug = printData.drugs[drugIdx];
                            
                            // Формируем основной текст лекарства
                            const drugText = `${drug.type_latin || ''} ${drug.active_substance_en || ''}${drug.dosageform ? ' ' + drug.dosageform : ''}`.trim();
                            const doseText = `${drug.dose || ''}`.trim();
                            const courseText = `S. ${drug.course || ''}`.trim();
                            
                            // Разбиваем длинный текст на две строки
                            const [firstLine, secondLine] = splitDrugText(drugText, 40);
                            
                            // Заполняем строки
                            const input1 = drugInputs[i * 3];
                            const input2 = drugInputs[i * 3 + 1];
                            const input3 = drugInputs[i * 3 + 2];
                            
                            if (input1) {
                                input1.value = firstLine;
                                input1.style.fontSize = '18px'; // Возвращаем стандартный размер шрифта
                            }
                            if (input2) {
                                // Если есть перенос, добавляем его в начало второй строки
                                input2.value = secondLine ? secondLine + ' ' + doseText : doseText;
                                input2.style.fontSize = '18px';
                            }
                            if (input3) {
                                input3.value = courseText;
                                input3.style.fontSize = '18px';
                            }
                            
                            // Добавляем отладочный вывод
                            console.log(`Drug ${i + 1}:`, {
                                firstLine,
                                secondLine,
                                doseText,
                                courseText
                            });
                        }
                    }
                });
            } catch (error) {
                console.error('Error initializing data:', error);
                alert('Ошибка при загрузке данных');
            }
        }

        // Обновим функцию preparePrint, чтобы сохранить размеры шрифта при печати
        function preparePrint() {
            document.querySelectorAll('.input-line').forEach(function(input) {
                const printDiv = document.getElementById('pv_' + input.name);
                if (printDiv) {
                    printDiv.textContent = input.value;
                    // Копируем размер шрифта из input в printDiv
                    printDiv.style.fontSize = input.style.fontSize;
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            initializeData();
            window.onbeforeprint = preparePrint;
        });
        
    </script>
</body>
</html> 
