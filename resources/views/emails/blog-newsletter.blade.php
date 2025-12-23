<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $post->title }} - Pet Shelter Blog</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        
        .email-header {
            background: linear-gradient(135deg, #AC5512 0%, #d4691b 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        
        .email-header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        
        .email-content {
            padding: 30px;
        }
        
        .post-title {
            color: #AC5512;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
            line-height: 1.3;
        }
        
        .post-description {
            color: #666;
            font-size: 18px;
            font-style: italic;
            margin-bottom: 25px;
            line-height: 1.5;
        }
        
        .post-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            color: #888;
            font-size: 14px;
        }
        
        .post-image {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .post-content {
            line-height: 1.8;
            color: #333;
            font-size: 16px;
        }
        
        .post-content h2 {
            color: #AC5512;
            margin: 30px 0 15px;
            font-size: 22px;
            font-weight: 600;
        }
        
        .post-content p {
            margin-bottom: 20px;
        }
        
        .post-content img {
            max-width: 100%;
            height: auto;
            border-radius: 6px;
            margin: 15px 0;
        }
        
        .post-content ul,
        .post-content ol {
            margin: 20px 0;
            padding-left: 25px;
        }
        
        .post-content li {
            margin-bottom: 8px;
        }
        
        .cta-button {
            display: inline-block;
            background-color: #AC5512;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            margin: 25px 0;
            text-align: center;
        }
        
        .email-footer {
            background-color: #f8f9fa;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        
        .footer-text {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        
        .unsubscribe-link {
            color: #AC5512;
            font-size: 13px;
            text-decoration: none;
        }
        
        @media (max-width: 600px) {
            .email-content {
                padding: 20px;
            }
            
            .post-title {
                font-size: 24px;
            }
            
            .post-description {
                font-size: 16px;
            }
            
            .post-meta {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>Pet Shelter Blog</h1>
            <p>Latest updates, stories, and tips from our shelter</p>
        </div>
        
        <!-- Content -->
        <div class="email-content">
            <h1 class="post-title">{{ $post->title }}</h1>
            
            <p class="post-description">{{ $post->description }}</p>
            
            <div class="post-meta">
                <div>Published: {{ $post->published_at->format('F j, Y') }}</div>
                <div>By: {{ $post->user->name }}</div>
            </div>
            
            @if($post->featured_image)
                <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="post-image">
            @endif
            
            <div class="post-content">
                {!! $post->content !!}
            </div>
            
            <a href="{{ route('blog.show', $post) }}" class="cta-button">
                Read Full Article on Website
            </a>
            
            <p style="font-size: 14px; color: #666; margin-top: 30px;">
                <em>You're receiving this email because you subscribed to the Pet Shelter Blog newsletter.</em>
            </p>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <p class="footer-text">
                &copy; {{ date('Y') }} Pet Shelter. All rights reserved.<br>
                123 Shelter Street, Petville, PV 12345
            </p>
            
            <p class="footer-text">
                <a href="{{ $unsubscribeUrl }}" class="unsubscribe-link">
                    Unsubscribe from this newsletter
                </a>
            </p>
        </div>
    </div>
</body>
</html>