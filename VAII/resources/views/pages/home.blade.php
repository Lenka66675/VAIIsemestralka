@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
    <div class="content-container">
        <div class="image-gallery">
            <div class="image-item">
                <img src="{{ asset('images/analyze.png') }}" alt="Analyze" class="image">
                <p class="image-caption">Analyze</p>
            </div>
            <div class="image-item">
                <img src="{{ asset('images/kpi-dashboard.png') }}" alt="Metrics" class="image">
                <p class="image-caption">Metrics</p>
            </div>
            <div class="image-item">
                <img src="{{ asset('images/segmentation.png') }}" alt="Segmentation" class="image">
                <p class="image-caption">Segmentation</p>
            </div>
            <div class="image-item">
                <img src="{{ asset('images/statistics.png') }}" alt="Statistics" class="image">
                <p class="image-caption">Statistics</p>
            </div>
            <div class="image-item">
                <img src="{{ asset('images/trends.png') }}" alt="Trends" class="image">
                <p class="image-caption">Trends</p>
            </div>


        </div>
        @auth
            @auth
                <div class="text-center mt-4">
                    <a href="/tasks" class="btn btn-primary custom-button">Tasks</a>
                    <a href="/projects" class="btn btn-primary custom-button">Projects</a>
                </div>
            @endauth



        @endauth
    </div>
@endsection
