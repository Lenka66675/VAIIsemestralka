@extends('layouts.app')

@section('title', 'Contact')

@section('content')
    <link rel="stylesheet" href="{{ asset('/css/contact.css') }}">
    <div class="container">
    <div class="row">
        <div class="col-md-6">
            <div class="contact-info">
                <h3>Contact Us</h3>
                <p><strong>Address:</strong> Povazska Bystrica</p>
                <p><strong>Phone:</strong> +421 900 123 456</p>
                <p><strong>Email:</strong> <a href="mailto:info@danfoss.sk">info@danfoss.sk</a></p>
                <p><strong>Working Hours:</strong></p>
                <ul>
                    <li>Monday - Friday: 9:00 AM - 6:00 PM</li>
                    <li>Saturday: 9:00 AM - 1:00 PM</li>
                    <li>Sunday: Closed</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="contact-form">
                <h3>Send a Message</h3>
                <form action="" method="post">
                    <div class="mb-3">
                        <input type="text" class="form-control" name="name" placeholder="Your Name" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" class="form-control" name="email" placeholder="Your Email" required>
                    </div>
                    <div class="mb-3">
                        <textarea class="form-control" name="message" rows="5" placeholder="Your Message" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Send Message</button>
                </form>
            </div>
        </div>
    </div>

    <div class="row map-container">
        <div class="col-md-12">
            <h3>Where to Find Us</h3>
            <div class="ratio ratio-16x9">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2611.2248292375834!2d18.410731776390204!3d49.12036507136889!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47148949ad433ee5%3A0xd14dc9b9b135cfaa!2sDanfoss%20Power%20Solutions%20a.s.!5e0!3m2!1ssk!2ssk!4v1729292382292!5m2!1ssk!2ssk" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection
