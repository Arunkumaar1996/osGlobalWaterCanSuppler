@extends('layouts.guest')

@section('title', 'Pure Drinking Water Can Doorstep Delivery')

@section('content')
    <!-- 1. Hero Section with Interactive Pincode Checker -->
    <x-landing.hero />

    <!-- 2. Dynamic Products Grid (20L, 10L, 5L cans) -->
    <x-landing.products-grid :products="$products" />

    <!-- 3. How It Works (4-Step Simple Process) -->
    <x-landing.how-it-works />

    <!-- 4. Why Choose Us (Pillars & Quality Standards) -->
    <x-landing.why-choose-us />

    <!-- 5. Delivery Service Areas & Covered Localities -->
    <x-landing.service-areas :deliveryZones="$deliveryZones" />

    <!-- 6. Customer Benefits & Modern Features -->
    <x-landing.benefits />

    <!-- 7. Customer Testimonials & Reviews -->
    <x-landing.testimonials :testimonials="$testimonials" />

    <!-- 8. Interactive FAQ Accordion -->
    <x-landing.faq :faqs="$faqs" />

    <!-- 9. Contact Section & Direct Inquiry Form -->
    <x-landing.contact />
@endsection
