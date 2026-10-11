<?php

// Lead forms other than the main quote form. Mirrors Fluent Forms 9 and 7 on the live site.
// Field types: text, email, tel, date, datetime-local, number, textarea, checkbox.
return [
    'wedding-quote' => [
        'form_key' => 'wedding_quote',
        'title' => 'Get A Free Quote [Wedding Chauffeur]',
        'subject' => 'New Wedding Chauffeur request from :name',
        'redirect' => 'thank_you',
        'submit' => 'Submit for Quote',
        'fields' => [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'width' => 'half', 'max' => 120],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'width' => 'half', 'max' => 190],
            ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'required' => true, 'width' => 'half', 'max' => 40],
            ['name' => 'wedding_date', 'label' => 'Wedding Date', 'type' => 'date', 'required' => true, 'width' => 'half'],
            ['name' => 'cars', 'label' => 'Number of cars required', 'type' => 'number', 'width' => 'half', 'min' => 1, 'max' => 50],
            ['name' => 'notes', 'label' => 'Additional info', 'type' => 'textarea', 'max' => 1500],
        ],
    ],

    'wedding-booking' => [
        'form_key' => 'wedding_booking',
        'title' => 'Wedding Car Hire',
        'subject' => 'New Wedding Car Hire booking form from :name',
        'redirect' => 'back',
        'success' => 'Thank you. We have received your wedding booking details and will be in touch shortly to confirm them.',
        'submit' => 'Submit Booking',
        'name_field' => 'brides_name',
        'fields' => [
            ['group' => 'Wedding'],
            ['name' => 'wedding_date', 'label' => 'Wedding Date', 'type' => 'date', 'width' => 'third'],
            ['name' => 'hours', 'label' => 'Hours/Transfer', 'type' => 'number', 'width' => 'third', 'min' => 0, 'max' => 99],
            ['name' => 'passengers', 'label' => 'Number of Passengers', 'type' => 'number', 'width' => 'third', 'min' => 0, 'max' => 99],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'max' => 190],

            ['group' => 'Bride'],
            ['name' => 'brides_name', 'label' => "Bride's Name", 'type' => 'text', 'width' => 'third', 'max' => 120],
            ['name' => 'brides_phone', 'label' => 'Mobile', 'type' => 'tel', 'width' => 'third', 'max' => 40],
            ['name' => 'brides_pickup_time', 'label' => 'Pick up time', 'type' => 'time', 'width' => 'third'],
            ['name' => 'brides_pickup_address', 'label' => "Bride's Pick up Address", 'type' => 'text', 'max' => 255],
            ['name' => 'brides_car', 'label' => 'Car(s) Requested', 'type' => 'text', 'max' => 190],
            ['name' => 'brides_contact', 'label' => 'Designated Contact', 'type' => 'text', 'width' => 'half', 'max' => 120],
            ['name' => 'brides_contact_mobile', 'label' => 'Designated Contact Mobile', 'type' => 'tel', 'width' => 'half', 'max' => 40],

            ['group' => 'Groom'],
            ['name' => 'grooms_name', 'label' => "Groom's Name", 'type' => 'text', 'width' => 'third', 'max' => 120],
            ['name' => 'grooms_phone', 'label' => 'Mobile', 'type' => 'tel', 'width' => 'third', 'max' => 40],
            ['name' => 'grooms_pickup_time', 'label' => 'Pick up time', 'type' => 'time', 'width' => 'third'],
            ['name' => 'grooms_pickup_address', 'label' => "Groom's Pick up Address", 'type' => 'text', 'max' => 255],
            ['name' => 'grooms_car', 'label' => 'Car(s) Requested', 'type' => 'text', 'max' => 190],
            ['name' => 'grooms_contact', 'label' => 'Designated Contact', 'type' => 'text', 'width' => 'half', 'max' => 120],
            ['name' => 'grooms_contact_mobile', 'label' => 'Designated Contact Mobile', 'type' => 'tel', 'width' => 'half', 'max' => 40],

            ['group' => 'Ceremony, photos and reception'],
            ['name' => 'ceremony_time', 'label' => 'Service Ceremony Time', 'type' => 'time', 'width' => 'half'],
            ['name' => 'ceremony_address', 'label' => 'Ceremony Address', 'type' => 'text', 'width' => 'half', 'max' => 255],
            ['name' => 'photo_address', 'label' => 'Photo Locations Address', 'type' => 'text', 'max' => 255],
            ['name' => 'reception_address', 'label' => 'Reception Address', 'type' => 'text', 'width' => 'half', 'max' => 255],
            ['name' => 'final_dropoff_time', 'label' => 'Drop Off Time', 'type' => 'time', 'width' => 'half'],
            ['name' => 'special_notes', 'label' => 'Special Instructions', 'type' => 'textarea', 'max' => 2000],

            ['group' => 'Gateway car'],
            ['name' => 'gateway_car', 'label' => 'Gateway Car', 'type' => 'text', 'width' => 'third', 'max' => 190],
            ['name' => 'gateway_pickup_time', 'label' => 'Pick Up Time', 'type' => 'time', 'width' => 'third'],
            ['name' => 'gateway_pickup_address', 'label' => 'Pick Up Address', 'type' => 'text', 'width' => 'third', 'max' => 255],

            ['name' => 'terms', 'label' => 'I accept the Booking Terms and Conditions below', 'type' => 'checkbox', 'required' => true],
        ],
    ],
];
