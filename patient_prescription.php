<?php
// Подключение к БД
$conn = pg_connect("host=localhost dbname=Legacy user=postgres password=password");
if (!$conn) {
    die('Ошибка подключения к базе данных');
}
pg_close($conn);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Выбор пациента и лекарств</title>
    <script src="./jquery-3.7.1.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 540px;
            margin: 40px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(60,60,120,0.13);
            padding: 38px 36px 32px 36px;
            position: relative;
            transition: box-shadow 0.3s;
        }
        .container:hover {
            box-shadow: 0 12px 40px rgba(60,60,120,0.18);
        }
        .header-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 18px;
        }
        .user-label {
            font-size: 16px;
            color: #2d3a5a;
            font-weight: 500;
            margin-right: 18px;
        }
        .logout-btn {
            padding: 7px 18px;
            background: #f5f5f5;
            border-radius: 7px;
            border: 1px solid #bbb;
            color: #333;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: background 0.18s, box-shadow 0.18s;
            box-shadow: 0 1px 4px #e0e7ff;
            cursor: pointer;
        }
        .logout-btn:hover {
            background: #e0e0e0;
            color: #222;
        }
        h2 {
            margin-top: 0;
            margin-bottom: 28px;
            font-size: 1.6em;
            font-weight: 700;
            color: #222;
            letter-spacing: 0.5px;
        }
        label {
            display: block;
            margin-top: 18px;
            font-weight: 500;
            color: #2d3a5a;
        }
        input[type=text] {
            width: 100%;
            font-size: 16px;
            padding: 8px 10px;
            margin-top: 6px;
            border-radius: 7px;
            border: 1px solid #cfd8dc;
            background: #f8fafc;
            transition: border 0.2s;
            box-sizing: border-box;
        }
        input[type=text]:focus {
            border: 1.5px solid #6c63ff;
            background: #fff;
            outline: none;
        }
        button, #add-drug-btn {
            margin-top: 18px;
            padding: 10px 24px;
            font-size: 17px;
            border-radius: 7px;
            border: none;
            background: linear-gradient(90deg, #6c63ff 0%, #5a5ae6 100%);
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px #e0e7ff;
            transition: background 0.2s;
            display: inline-block;
        }
        button:hover, #add-drug-btn:hover {
            background: linear-gradient(90deg, #5a5ae6 0%, #6c63ff 100%);
        }
        #add-drug-btn {
            background: #f5f5f5;
            color: #333;
            border: 1px solid #bbb;
            box-shadow: none;
            margin-top: 10px;
            margin-bottom: 0;
        }
        #add-drug-btn:hover {
            background: #e0e0e0;
            color: #222;
        }
        #next-btn {
            width: 100%;
            margin-top: 30px;
            font-size: 19px;
            padding: 12px 0;
        }
        @media (max-width: 600px) {
            .container {
                margin: 16px 4px;
                padding: 18px 4vw 18px 4vw;
            }
            h2 { font-size: 1.1em; }
        }
        .drugs-list { margin-top: 18px; }
        .drug-row { display: flex; align-items: center; margin-bottom: 10px; }
        .drug-row input[type=text] { flex: 1; }
        .drug-row button { margin-left: 10px; }
        #add-drug-btn { margin-top: 10px; }
        #next-btn { margin-top: 30px; padding: 10px 30px; font-size: 18px; }
        .autocomplete-list {
            position: absolute;
            background: #fff;
            border: 1px solid #ccc;
            max-height: 180px;
            overflow-y: auto;
            z-index: 1000;
            width: 100%;
            box-sizing: border-box;
        }
        .autocomplete-item {
            padding: 8px 12px;
            cursor: pointer;
        }
        .autocomplete-item:hover, .autocomplete-item.active {
            background: #e0e0e0;
        }
        .autocomplete-wrap { position: relative; }
        .clear-input-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            font-size: 22px;
            color: #888;
            cursor: pointer;
            padding: 0 4px;
            z-index: 2;
            line-height: 1;
        }
        .clear-input-btn:hover {
            color: #d00;
        }
    </style>
</head>
<body>
<script>
    sessionStorage.clear();
</script>
<div class="container">
    <div class="header-bar">
        <span class="user-label">Пользователь: <?= $fio ?></span>
        <a href="logout.php" class="logout-btn">Выход</a>
    </div>
    <h2>Выбор пациента и лекарственных средств</h2>
    <form id="prescriptionForm" method="post" action="#">
        <label for="patient_search">Пациент:</label>
        <div class="autocomplete-wrap" style="position:relative;">
            <input type="text" id="patient_search" name="patient_search" placeholder="Введите ФИО или код пациента" autocomplete="off" required>
            <button type="button" id="clear-patient-btn" class="clear-input-btn" style="display:none;" tabindex="-1">&times;</button>
            <input type="hidden" id="patient_code" name="patient">
            <div id="autocomplete-list" class="autocomplete-list" style="display:none;"></div>
        </div>
        <div class="drugs-list" id="drugs-list">
            <label>Лекарственные средства:</label>
            <div class="drug-row">
                <div class="autocomplete-wrap">
                    <input type="text" name="drugs[]" class="drug-autocomplete" placeholder="Введите название или действующее вещество" autocomplete="off" required>
                    <div class="autocomplete-list" style="display:none;"></div>
                </div>
                <div class="drug-options-wrap" style="display:none; margin-top: 10px;">
                    <select name="drug_types[]" class="drug-type-select" style="width: 100%; padding: 8px; border-radius: 7px; border: 1px solid #cfd8dc; margin-bottom: 10px;">
                        <option value="">Выберите тип</option>
                        <option value="Таблетки" data-latin="Tab.">Таблетки</option>
                        <option value="Капсулы" data-latin="Cap.">Капсулы</option>
                        <option value="Капли" data-latin="Sol.">Капли \ Раствор</option>
                        <option value="Гель" data-latin="Gel.">Гель</option>
                        <option value="Мазь" data-latin="Ung.">Мазь</option>
                    </select>
                    <select name="drug_dosageform[]" class="drug-dosageform-select" style="width: 100%; padding: 8px; border-radius: 7px; border: 1px solid #cfd8dc; margin-bottom: 10px; display:none;">
                        <option value="">Выберите форму/граммовку</option>
                    </select>
                    <select name="drug_courses[]" class="drug-course-select" style="width: 100%; padding: 8px; border-radius: 7px; border: 1px solid #cfd8dc; margin-bottom: 10px;">
                        <option value="">Выберите курс</option>
                        <option value="По 1 таблетке 3 раза в день" data-type="Таблетки">По 1 таблетке 3 раза в день</option>
                        <option value="По 2 таблетки 2 раза в день" data-type="Таблетки">По 2 таблетки 2 раза в день</option>
                        <option value="По 1 таблетке 2 раза в день" data-type="Таблетки">По 1 таблетке 2 раза в день</option>
                        <option value="По 1 таблетке 1 раз в день" data-type="Таблетки">По 1 таблетке 1 раз в день</option>
                        <option value="По 1 капсуле 3 раза в день" data-type="Капсулы">По 1 капсуле 3 раза в день</option>
                        <option value="По 2 капсулы 2 раза в день" data-type="Капсулы">По 2 капсулы 2 раза в день</option>
                        <option value="По 1 капсуле 2 раза в день" data-type="Капсулы">По 1 капсуле 2 раза в день</option>
                        <option value="По 1 капсуле 1 раз в день" data-type="Капсулы">По 1 капсуле 1 раз в день</option>
                        <option value="По 2 капли 3 раза в день" data-type="Капли">По 2 капли 3 раза в день</option>
                        <option value="По 1 капле 2 раза в день" data-type="Капли">По 1 капле 2 раза в день</option>
                        <option value="По 2 мл 3 раза в день" data-type="Капли">По 2 мл 3 раза в день</option>
                        <option value="По 1 мл 2 раза в день" data-type="Капли">По 1 мл 2 раза в день</option>
                        <option value="Наносить гель тонким слоем 2 раза в день" data-type="Гель">Наносить гель тонким слоем 2 раза в день</option>
                        <option value="Наносить гель тонким слоем 3 раза в день" data-type="Гель">Наносить гель тонким слоем 3 раза в день</option>
                        <option value="Наносить мазь тонким слоем 2 раза в день" data-type="Мазь">Наносить мазь тонким слоем 2 раза в день</option>
                        <option value="Наносить мазь тонким слоем 3 раза в день" data-type="Мазь">Наносить мазь тонким слоем 3 раза в день</option>
                    </select>
                    <select name="doses[]" class="doses-select" style="width: 100%; padding: 8px; border-radius: 7px; border: 1px solid #cfd8dc;">
                        <option value="">Дозировка</option>
                        <option value="D.t.d.№10">D.t.d.№10</option>
                        <option value="D.t.d.№20">D.t.d.№20</option>
                        <option value="D.t.d.№30">D.t.d.№30</option>
                        <option value="D.t.d.№40">D.t.d.№40</option>
                        <option value="D.t.d.№50">D.t.d.№50</option>
                        <option value="D.t.d.№60">D.t.d.№60</option>
                    </select>
                </div>
            </div>
        </div>
        <button type="button" id="add-drug-btn">+ Добавить лекарство</button>
        <br>
        <button type="submit" id="next-btn">Далее</button>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Очищаем все select и input в .drug-row при обновлении страницы
        document.querySelectorAll('.drug-row').forEach(function(row) {
            row.querySelectorAll('input[type="text"]').forEach(function(inp) { inp.value = ''; });
            row.querySelectorAll('select').forEach(function(sel) { sel.selectedIndex = 0; });
            // Скрыть dosageform select
            var dosageSel = row.querySelector('.drug-dosageform-select');
            if (dosageSel) dosageSel.style.display = 'none';
        });
    });
    // --- Autocomplete для пациента ---
    const searchInput = document.getElementById('patient_search');
    const codeInput = document.getElementById('patient_code');
    const listDiv = document.getElementById('autocomplete-list');
    let activeIdx = -1;
    const clearBtn = document.getElementById('clear-patient-btn');
    searchInput.addEventListener('input', function() {
        clearBtn.style.display = this.value ? 'block' : 'none';
        const q = this.value.trim();
        codeInput.value = '';
        if (q.length < 2) { listDiv.style.display = 'none'; return; }
        fetch('patient_search.php?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(arr => {
                listDiv.innerHTML = '';
                if (!arr.length) { listDiv.style.display = 'none'; return; }
                arr.forEach((pat, idx) => {
                    // Формируем строку: ФИО, номер, СК
                    const patientNumber = pat.number || '';
                    const sk = pat.ptpart_code ? `СК-${pat.ptpart_code}` : '';
                    const displayText = `${pat.fio} — ${patientNumber}${sk ? ' — ' + sk : ''}`;
                    const div = document.createElement('div');
                    div.className = 'autocomplete-item';
                    div.textContent = displayText;
                    div.onclick = function() {
                        // Сохраняем данные пациента в sessionStorage
                        try {
                            sessionStorage.setItem('selectedPatient', JSON.stringify(pat));
                            console.log('Stored patient data:', pat);
                        } catch (e) {
                            console.error('Error storing patient data:', e);
                        }
                        // В поле поиска только ФИО
                        searchInput.value = pat.fio;
                        codeInput.value = patientNumber;
                        listDiv.style.display = 'none';
                    };
                    listDiv.appendChild(div);
                });
                listDiv.style.display = 'block';
                activeIdx = -1;
            });
    });
    // Навигация по стрелкам и Enter
    searchInput.addEventListener('keydown', function(e) {
        const items = listDiv.querySelectorAll('.autocomplete-item');
        if (!items.length) return;
        if (e.key === 'ArrowDown') {
            activeIdx = (activeIdx + 1) % items.length;
            items.forEach((it, i) => it.classList.toggle('active', i === activeIdx));
            e.preventDefault();
        } else if (e.key === 'ArrowUp') {
            activeIdx = (activeIdx - 1 + items.length) % items.length;
            items.forEach((it, i) => it.classList.toggle('active', i === activeIdx));
            e.preventDefault();
        } else if (e.key === 'Enter') {
            if (activeIdx >= 0) {
                items[activeIdx].click();
                e.preventDefault();
            }
        }
    });
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !listDiv.contains(e.target)) {
            listDiv.style.display = 'none';
        }
    });
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        codeInput.value = '';
        clearBtn.style.display = 'none';
        listDiv.style.display = 'none';
        sessionStorage.removeItem('selectedPatient');
        searchInput.focus();
    });

    // --- Autocomplete для лекарств ---
    function setupDrugAutocomplete(input) {
        let activeIdx = -1;
        const wrap = input.parentElement;
        const list = wrap.querySelector('.autocomplete-list');
        const optionsWrap = wrap.parentElement.querySelector('.drug-options-wrap');
        const typeSelect = wrap.parentElement.querySelector('.drug-type-select');
        const courseSelect = wrap.parentElement.querySelector('.drug-course-select');
        const dosesSelect = wrap.parentElement.querySelector('.doses-select');
        const dosageFormSelect = wrap.parentElement.querySelector('.drug-dosageform-select');

        // --- Только фильтрация курсов по типу ---
        typeSelect.addEventListener('change', function() {
            filterCourseOptions(typeSelect, courseSelect);
        });

        // --- Автодополнение по лекарствам ---
        input.addEventListener('input', function() {
            const q = this.value.trim();
            if (q.length < 2) { list.style.display = 'none'; return; }
            fetch('drug_search.php?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(arr => {
                    list.innerHTML = '';
                    if (!arr.length) { list.style.display = 'none'; return; }
                    arr.forEach((drug, idx) => {
                        const div = document.createElement('div');
                        div.className = 'autocomplete-item';
                        div.textContent = drug.trade_name + 
                            (drug.active_substance_ru ? ' (' + drug.active_substance_ru + ')' : '');
                        div.onclick = function() {
                            input.value = drug.trade_name + 
                                (drug.active_substance_ru ? ' (' + drug.active_substance_ru + ')' : '');
                            list.style.display = 'none';
                            optionsWrap.style.display = 'block';
                            // --- ДОБАВЛЕНО: Запрос форм/граммовок ---
                            fetch('drug_dosageform.php?trade_name=' + encodeURIComponent(drug.trade_name))
                                .then(r => r.json())
                                .then(forms => {
                                    dosageFormSelect.innerHTML = '<option value="">Выберите форму/граммовку</option>';
                                    if (forms && forms.length > 0) {
                                        forms.forEach(f => {
                                            const opt = document.createElement('option');
                                            opt.value = f;
                                            opt.textContent = f;
                                            dosageFormSelect.appendChild(opt);
                                        });
                                        dosageFormSelect.style.display = '';
                                    } else {
                                        dosageFormSelect.style.display = 'none';
                                    }
                                });
                            // ---
                            // Сохраняем выбранное лекарство в sessionStorage (обновлено)
                            try {
                                const currentDrugs = JSON.parse(sessionStorage.getItem('selectedDrugs') || '[]');
                                const drugData = {
                                    active_substance_en: drug.active_substance_en,
                                    trade_name: drug.trade_name,
                                    active_substance: drug.active_substance_ru,
                                    type: typeSelect.value,
                                    type_latin: typeSelect.options[typeSelect.selectedIndex].getAttribute('data-latin'),
                                    dosageform: dosageFormSelect.value,
                                    course: courseSelect.value,
                                    dose: dosesSelect.value
                                };
                                // Обновляем или добавляем запись
                                const existingIndex = currentDrugs.findIndex(d => d.trade_name === drug.trade_name);
                                if (existingIndex >= 0) {
                                    currentDrugs[existingIndex] = drugData;
                                } else {
                                    currentDrugs.push(drugData);
                                }
                                sessionStorage.setItem('selectedDrugs', JSON.stringify(currentDrugs));
                            } catch (e) {
                                console.error('Error storing drug data:', e);
                            }
                        };
                        list.appendChild(div);
                    });
                    list.style.display = 'block';
                    activeIdx = -1;
                });
        });

        // --- При выборе формы/граммовки сохраняем в sessionStorage ---
        dosageFormSelect.addEventListener('change', function() {
            try {
                const currentDrugs = JSON.parse(sessionStorage.getItem('selectedDrugs') || '[]');
                const drugName = input.value.split(' (')[0];
                const drugData = currentDrugs.find(d => d.trade_name === drugName);
                if (drugData) {
                    drugData.dosageform = this.value;
                    sessionStorage.setItem('selectedDrugs', JSON.stringify(currentDrugs));
                }
            } catch (e) {
                console.error('Error updating drug dosageform:', e);
            }
        });

        // --- При выборе дозировки сохраняем в sessionStorage ---
        dosesSelect.addEventListener('change', function() {
            try {
                const currentDrugs = JSON.parse(sessionStorage.getItem('selectedDrugs') || '[]');
                const drugName = input.value.split(' (')[0];
                const drugData = currentDrugs.find(d => d.trade_name === drugName);
                if (drugData) {
                    drugData.dose = this.value;
                    sessionStorage.setItem('selectedDrugs', JSON.stringify(currentDrugs));
                }
            } catch (e) {
                console.error('Error updating drug dose:', e);
            }
        });

        // --- При выборе курса сохраняем в sessionStorage ---
        courseSelect.addEventListener('change', function() {
            try {
                const currentDrugs = JSON.parse(sessionStorage.getItem('selectedDrugs') || '[]');
                const drugName = input.value.split(' (')[0];
                const drugData = currentDrugs.find(d => d.trade_name === drugName);
                if (drugData) {
                    drugData.course = this.value;
                    sessionStorage.setItem('selectedDrugs', JSON.stringify(currentDrugs));
                }
            } catch (e) {
                console.error('Error updating drug course:', e);
            }
        });

        // Навигация по стрелкам и Enter
        input.addEventListener('keydown', function(e) {
            const items = list.querySelectorAll('.autocomplete-item');
            if (!items.length) return;
            if (e.key === 'ArrowDown') {
                activeIdx = (activeIdx + 1) % items.length;
                items.forEach((it, i) => it.classList.toggle('active', i === activeIdx));
                e.preventDefault();
            } else if (e.key === 'ArrowUp') {
                activeIdx = (activeIdx - 1 + items.length) % items.length;
                items.forEach((it, i) => it.classList.toggle('active', i === activeIdx));
                e.preventDefault();
            } else if (e.key === 'Enter') {
                if (activeIdx >= 0) {
                    items[activeIdx].click();
                    e.preventDefault();
                }
            }
        });
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !list.contains(e.target)) {
                list.style.display = 'none';
            }
        });

        // При инициализации сразу фильтруем (на случай если строка добавлена динамически)
        filterCourseOptions(typeSelect, courseSelect);
    }
    // Инициализация для первого поля
    document.querySelectorAll('.drug-autocomplete').forEach(setupDrugAutocomplete);
    // --- Динамическое добавление лекарств ---
    const maxDrugs = 12;
    document.getElementById('add-drug-btn').onclick = function() {
        const drugsList = document.getElementById('drugs-list');
        const current = drugsList.querySelectorAll('.drug-row').length;
        if (current >= maxDrugs) return;
        const firstRow = drugsList.querySelector('.drug-row');
        const newRow = firstRow.cloneNode(true);
        const newInput = newRow.querySelector('input[type=text]');
        newInput.value = '';
        setupDrugAutocomplete(newInput);

        // Фильтрация для новой строки
        const typeSelect = newRow.querySelector('.drug-type-select');
        const courseSelect = newRow.querySelector('.drug-course-select');
        filterCourseOptions(typeSelect, courseSelect);

        drugsList.appendChild(newRow);
    };
    // Валидация: не отправлять форму, если не выбран пациент
    document.getElementById('prescriptionForm').onsubmit = function() {
        if (!codeInput.value) {
            alert('Пожалуйста, выберите пациента из списка!');
            searchInput.focus();
            return false;
        }
        return true;
    };

    // Обработчик поиска пациента
    $('#patientSearch').on('input', function() {
        const query = $(this).val();
        if (query.length < 2) {
            $('#searchResults').hide();
            return;
        }

        $.get('patient_search.php', { q: query }, function(data) {
            console.log('Received patient data:', data);
            
            const results = $('#searchResults');
            results.empty();
            
            data.forEach(function(patient) {
                // Формируем строку для выпадающего списка: ФИО, номер, СК
                const patientNumber = patient.number || '';
                const sk = patient.ptpart_code ? `СК-${patient.ptpart_code}` : '';
                const displayText = `${patient.fio} — ${patientNumber}${sk ? ' — ' + sk : ''}`;
                const item = $('<div>')
                    .addClass('patient-item')
                    .text(displayText);
                
                // Добавляем обработчик клика
                item.on('click', function() {
                    console.log('Clicked patient data:', patient);
                    
                    // Проверяем наличие всех необходимых полей
                    const requiredFields = ['lst_name', 'fst_name', 'ptr_name', 'birth_date', 'ptpart_code', 'ptrang_id', 'ptstat_id', 'pat_number', 'pat_code'];
                    const missingFields = requiredFields.filter(field => !patient[field]);
                    
                    if (missingFields.length > 0) {
                        console.error('Missing required fields:', missingFields);
                        alert('Ошибка: отсутствуют необходимые данные пациента');
                        return;
                    }
                    
                    // Сохраняем данные в sessionStorage
                    try {
                        sessionStorage.setItem('selectedPatient', JSON.stringify(patient));
                        const storedData = sessionStorage.getItem('selectedPatient');
                        console.log('Stored patient data:', storedData);
                        
                        if (!storedData) {
                            throw new Error('Failed to store patient data');
                        }
                    } catch (e) {
                        console.error('Error storing patient data:', e);
                        alert('Ошибка при сохранении данных пациента');
                        return;
                    }
                    
                    // Обновляем поля формы с правильным номером
                    $('#patientSearch').val(patient.fio + ' (' + patientNumber + ')');
                    $('#patientCode').val(patientNumber);
                    
                    // Скрываем результаты поиска
                    $('#searchResults').hide();
                });
                
                results.append(item);
            });
            
            results.show();
        });
    });

    // Обработчик отправки формы
    $('#prescriptionForm').on('submit', function(e) {
        e.preventDefault();
        
        const storedPatient = sessionStorage.getItem('selectedPatient');
        const storedDrugs = sessionStorage.getItem('selectedDrugs');
        
        if (!storedPatient) {
            alert('Пожалуйста, выберите пациента');
            return;
        }

        try {
            const patientData = JSON.parse(storedPatient);
            const drugsData = JSON.parse(storedDrugs || '[]');
            
            // Формируем данные о пациенте
            const ptstatMap = {
                35: "статус",
                36: "статус",
                37: "статус",
                50: "статус",
                55: "статус",
                56: "статус",
                60: "статус",
                61: "статус",
                70: "статус",
                80: "статус",
                90: "статус",
                100: "статус",
                101: "статус"
            };

            let rangAddition = "";
            if ([30, 31, 32, 33].includes(Number(patientData.ptstat_id))) {
                rangAddition = "оф. кадр.";
            } else if (ptstatMap[patientData.ptstat_id]) {
                rangAddition = ptstatMap[patientData.ptstat_id];
            }
            const ptrang_name_full = patientData.ptrang_name + (rangAddition ? " " + rangAddition : "");

            const patientInfo = {
                fio: `${patientData.lst_name} ${patientData.fst_name} ${patientData.ptr_name}`,
                birth_date: formatDate(patientData.birth_date),
                ptpart_code: patientData.ptpart_code,
                ptrang: patientData.ptrang_id,
                ptrang_name: ptrang_name_full,
                number: [30, 31, 32, 33].includes(parseInt(patientData.ptstat_id)) ? patientData.pat_number : patientData.pat_code
            };

            // Сохраняем данные в sessionStorage
            const printData = {
                patient: patientInfo,
                drugs: drugsData
            };

            sessionStorage.setItem('printData', JSON.stringify(printData));

            // Перенаправляем на страницу печати
            window.location.href = 'print_form.php';
        } catch (e) {
            console.error('Error processing data:', e);
            alert('Ошибка при обработке данных');
        }
    });

    // Вспомогательная функция для форматирования даты
    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('ru-RU', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        }).replace(/\./g, '.');
    }

    function filterCourseOptions(typeSelect, courseSelect) {
        const selectedType = typeSelect.value;
        Array.from(courseSelect.options).forEach(option => {
            if (!option.value) {
                option.style.display = '';
                return;
            }
            const types = (option.getAttribute('data-type') || '').split(',');
            option.style.display = types.includes(selectedType) ? '' : 'none';
        });
        // Сбросить выбор, если текущий курс не подходит
        if (courseSelect.selectedOptions.length && courseSelect.selectedOptions[0].style.display === 'none') {
            courseSelect.selectedIndex = 0;
        }
    }
</script>
</body>
</html> 
