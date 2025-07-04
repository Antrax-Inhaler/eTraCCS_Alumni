
<div class="container">
    <a href="{{ route('resume.download') }}" class="download-btn">
        <i class="fas fa-download"></i> Download PDF
    </a>

    <div class="resume-container" id="resumeContent">
        <!-- The same content as before -->
        @include('resume.pdf') <!-- Include the PDF content -->
    </div>
</div>

@push('styles')
<style>
    /* Your existing CSS styles */
    .download-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #3a86ff;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 50px;
        cursor: pointer;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(58, 134, 255, 0.4);
        display: flex;
        align-items: center;
        gap: 8px;
        z-index: 100;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    
    .download-btn:hover {
        background: #8338ec;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(131, 56, 236, 0.4);
    }
    
    .container {
        display: flex;
        justify-content: center;
        padding: 20px;
    }
    
    .resume-container {
        width: 8.5in;
        height: 13in;
    }
</style>
@endpush