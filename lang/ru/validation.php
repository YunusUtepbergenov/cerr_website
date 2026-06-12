<?php

$translationAttributes = [];

foreach (['kr' => 'KR', 'uz' => 'UZ', 'ru' => 'RU', 'en' => 'EN'] as $locale => $label) {
    $translationAttributes["translations.$locale.title"] = "заголовок ($label)";
    $translationAttributes["translations.$locale.short_description"] = "краткое описание ($label)";
    $translationAttributes["translations.$locale.content"] = "содержание ($label)";
    $translationAttributes["translations.$locale.seo_title"] = "SEO заголовок ($label)";
    $translationAttributes["translations.$locale.seo_description"] = "SEO описание ($label)";
    $translationAttributes["names.$locale"] = "название ($label)";
    $translationAttributes["cover_uploads.$locale"] = "обложка ($label)";
    $translationAttributes["titles.$locale"] = "заголовок ($label)";
    $translationAttributes["contents.$locale"] = "содержание ($label)";
}

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines (ru)
    |--------------------------------------------------------------------------
    */

    'accepted' => 'Поле :attribute должно быть принято.',
    'active_url' => 'Поле :attribute должно быть действительным URL.',
    'after' => 'Поле :attribute должно быть датой после :date.',
    'after_or_equal' => 'Поле :attribute должно быть датой после или равной :date.',
    'alpha' => 'Поле :attribute может содержать только буквы.',
    'alpha_dash' => 'Поле :attribute может содержать только буквы, цифры, дефисы и подчёркивания.',
    'alpha_num' => 'Поле :attribute может содержать только буквы и цифры.',
    'array' => 'Поле :attribute должно быть массивом.',
    'before' => 'Поле :attribute должно быть датой до :date.',
    'before_or_equal' => 'Поле :attribute должно быть датой до или равной :date.',
    'between' => [
        'array' => 'Поле :attribute должно содержать от :min до :max элементов.',
        'file' => 'Размер файла :attribute должен быть от :min до :max КБ.',
        'numeric' => 'Поле :attribute должно быть от :min до :max.',
        'string' => 'Длина поля :attribute должна быть от :min до :max символов.',
    ],
    'boolean' => 'Поле :attribute должно иметь значение «да» или «нет».',
    'confirmed' => 'Поле :attribute не совпадает с подтверждением.',
    'current_password' => 'Неверный пароль.',
    'date' => 'Поле :attribute должно быть корректной датой.',
    'date_format' => 'Поле :attribute не соответствует формату :format.',
    'declined' => 'Поле :attribute должно быть отклонено.',
    'different' => 'Поля :attribute и :other должны различаться.',
    'digits' => 'Поле :attribute должно содержать :digits цифр.',
    'digits_between' => 'Поле :attribute должно содержать от :min до :max цифр.',
    'dimensions' => 'Изображение :attribute имеет недопустимые размеры.',
    'distinct' => 'Поле :attribute содержит повторяющееся значение.',
    'email' => 'Поле :attribute должно быть действительным email-адресом.',
    'ends_with' => 'Поле :attribute должно заканчиваться одним из значений: :values.',
    'exists' => 'Выбранное значение поля :attribute не найдено.',
    'file' => 'Поле :attribute должно быть файлом.',
    'filled' => 'Поле :attribute должно быть заполнено.',
    'gt' => [
        'array' => 'Поле :attribute должно содержать более :value элементов.',
        'file' => 'Размер файла :attribute должен быть больше :value КБ.',
        'numeric' => 'Поле :attribute должно быть больше :value.',
        'string' => 'Длина поля :attribute должна быть больше :value символов.',
    ],
    'gte' => [
        'array' => 'Поле :attribute должно содержать :value и более элементов.',
        'file' => 'Размер файла :attribute должен быть не меньше :value КБ.',
        'numeric' => 'Поле :attribute должно быть не меньше :value.',
        'string' => 'Длина поля :attribute должна быть не меньше :value символов.',
    ],
    'image' => 'Поле :attribute должно быть изображением.',
    'in' => 'Выбрано недопустимое значение поля :attribute.',
    'integer' => 'Поле :attribute должно быть целым числом.',
    'ip' => 'Поле :attribute должно быть действительным IP-адресом.',
    'json' => 'Поле :attribute должно быть корректной JSON-строкой.',
    'lt' => [
        'array' => 'Поле :attribute должно содержать менее :value элементов.',
        'file' => 'Размер файла :attribute должен быть меньше :value КБ.',
        'numeric' => 'Поле :attribute должно быть меньше :value.',
        'string' => 'Длина поля :attribute должна быть меньше :value символов.',
    ],
    'lte' => [
        'array' => 'Поле :attribute должно содержать не более :value элементов.',
        'file' => 'Размер файла :attribute должен быть не больше :value КБ.',
        'numeric' => 'Поле :attribute должно быть не больше :value.',
        'string' => 'Длина поля :attribute должна быть не больше :value символов.',
    ],
    'max' => [
        'array' => 'Поле :attribute должно содержать не более :max элементов.',
        'file' => 'Размер файла :attribute должен быть не больше :max КБ.',
        'numeric' => 'Поле :attribute должно быть не больше :max.',
        'string' => 'Длина поля :attribute должна быть не больше :max символов.',
    ],
    'mimes' => 'Поле :attribute должно быть файлом одного из типов: :values.',
    'mimetypes' => 'Поле :attribute должно быть файлом одного из типов: :values.',
    'min' => [
        'array' => 'Поле :attribute должно содержать не менее :min элементов.',
        'file' => 'Размер файла :attribute должен быть не меньше :min КБ.',
        'numeric' => 'Поле :attribute должно быть не меньше :min.',
        'string' => 'Длина поля :attribute должна быть не меньше :min символов.',
    ],
    'not_in' => 'Выбрано недопустимое значение поля :attribute.',
    'numeric' => 'Поле :attribute должно быть числом.',
    'present' => 'Поле :attribute должно присутствовать.',
    'regex' => 'Поле :attribute имеет некорректный формат.',
    'required' => 'Поле :attribute обязательно для заполнения.',
    'required_if' => 'Поле :attribute обязательно, когда :other равно :value.',
    'required_unless' => 'Поле :attribute обязательно, когда :other не равно :values.',
    'required_with' => 'Поле :attribute обязательно, когда указано :values.',
    'required_without' => 'Поле :attribute обязательно, когда не указано :values.',
    'same' => 'Поля :attribute и :other должны совпадать.',
    'size' => [
        'array' => 'Поле :attribute должно содержать :size элементов.',
        'file' => 'Размер файла :attribute должен быть :size КБ.',
        'numeric' => 'Поле :attribute должно быть равно :size.',
        'string' => 'Длина поля :attribute должна быть :size символов.',
    ],
    'starts_with' => 'Поле :attribute должно начинаться с одного из значений: :values.',
    'string' => 'Поле :attribute должно быть строкой.',
    'timezone' => 'Поле :attribute должно быть действительным часовым поясом.',
    'unique' => 'Такое значение поля :attribute уже существует.',
    'uploaded' => 'Не удалось загрузить файл :attribute.',
    'url' => 'Поле :attribute должно быть действительным URL.',
    'uuid' => 'Поле :attribute должно быть корректным UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => array_merge([
        'name' => 'название',
        'email' => 'email',
        'password' => 'пароль',
        'role' => 'роль',
        'slug' => 'slug',
        'title' => 'заголовок',
        'url' => 'URL',
        'status' => 'статус',
        'scheduled_at' => 'время публикации',
        'category_id' => 'категория',
        'tag_ids' => 'теги',
        'image' => 'изображение',
        'imageUpload' => 'изображение',
        'file' => 'файл',
        'folder' => 'папка',
    ], $translationAttributes),

];
