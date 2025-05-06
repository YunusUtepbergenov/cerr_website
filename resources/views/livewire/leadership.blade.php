@push('styles')
<style>
    body {
        background: linear-gradient(135deg, #f6f9ff, #f4f4f4);
        font-family: 'Poppins', sans-serif;
    }
    
    h1.gradient-text {
        background: linear-gradient(to right, #373c49, #182848);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 3rem;
        letter-spacing: 1px;
    }
    
    .team-card {
        transition: all 0.4s ease;
        border: 1px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(20px);
    }
    
    .team-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.1);
    }
    
    .team-img {
        width: 260px;
        height: 300px;
        object-fit: cover;
        border: 4px solid #ffffff;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
        border-radius: 15%;
    }
    
    /* On medium devices (tablets), reduce the size */
    @media (max-width: 992px) {
    .team-img {
        width: 240px;
        height: 270px;
    }
    }
    
    /* On small devices (phones), reduce even more */
    @media (max-width: 576px) {
    .team-img {
        width: 180px;
        height: 200px;
        margin-bottom: 20px;
    }
    
    .team-card {
        flex-direction: column;
        text-align: center;
    }
    }
    
    
    .team-card:hover .team-img {
        transform: scale(1.05);
    }
    
    .glass-card {
        background: rgba(255, 255, 255, 0.5);
    }
    
    .icon-gradient {
        background: linear-gradient(to right, #4b6cb7, #182848);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    p {
        font-size: 2rem;
    }
    
    .shadow {
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
    }

</style>
@endpush
<div>
    <section class="echo-hero-section inner inner-post inner-post-3">
        <div class="echo-hero">
            <div class="container py-5">
                <div class="row mb-5">
                  <div class="col-12">
                    <div class="team-card d-flex align-items-center p-4 shadow rounded-5 glass-card">
                      <img src="{{asset('images/leadership/obidarzikulovich.jpg')}}" class="team-img me-4" alt="@lang('obid_arzikulovich')">
                      <div class="ps-lg-5">
                        <h2 class="fw-bold mb-1">@lang('leadership.obid_arzikulovich')</h2>
                        <div class="mt-3">
                          <p class="text-secondary mb-10">@lang('leadership.director')</p>
                        </div>
                        <div class="mt-5">
                          <p class="d-inline mb-1 p-2"><i class="fas fa-phone-alt me-2"></i>+998 78 150-02-02</p>
                          <p class="d-inline p-2"><i class="fas fa-envelope icon-gradient me-2"></i>info@cerr.uz</p>  
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="row mb-5">
                  <div class="col-12">
                    <div class="team-card d-flex align-items-center p-4 shadow rounded-5 glass-card">
                      <img src="{{asset('images/leadership/asadov.JPG')}}" class="team-img me-4" alt="Асадов Хуршед Саъдуллаевич">
                      <div class="ps-lg-5">
                        <h2 class="fw-bold mb-1">@lang('leadership.khurshed_saadullaevich')</h2>
                        <div class="mt-3">
                          <p class="text-secondary mb-10">@lang('leadership.deputy')</p>
                        </div>
                        <div class="mt-5">
                          <p class="d-inline mb-1 p-2"><i class="fas fa-phone-alt me-2"></i>+998 78 150-02-02</p>
                          <p class="d-inline p-2"><i class="fas fa-envelope icon-gradient me-2"></i>info@cerr.uz</p>  
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row mb-5">
                    <div class="col-12">
                      <div class="team-card d-flex align-items-center p-4 shadow rounded-5 glass-card">
                        <img src="{{asset('images/leadership/ortiqov.jpg')}}" class="team-img me-4" alt="Асадов Хуршед Саъдуллаевич">
                        <div class="ps-lg-5">
                          <h2 class="fw-bold mb-1">@lang('leadership.nozimjon_kozimjonovich')</h2>
                          <div class="mt-3">
                            <p class="text-secondary mb-10">@lang('leadership.deputy')</p>
                          </div>
                          <div class="mt-5">
                            <p class="d-inline mb-1 p-2"><i class="fas fa-phone-alt me-2"></i>+998 78 150-02-02</p>
                            <p class="d-inline p-2"><i class="fas fa-envelope icon-gradient me-2"></i>info@cerr.uz</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
            </div>
        </div>            
    </section>
</div>
