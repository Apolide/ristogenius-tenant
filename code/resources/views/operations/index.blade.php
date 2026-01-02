@extends('layouts.app')
@section('content')

<div class="content">
    <!-- Start::main-content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="md:flex block items-center justify-between mb-6 mt-[2rem]  page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">Operations</h5>
                <nav>
                    <ol class="flex items-center whitespace-nowrap min-w-0">
                    <li class="text-[12px]"> <a class="flex items-center text-primary hover:text-primary"
                        href="/"> Home <i
                            class="ti ti-chevrons-right flex-shrink-0 mx-3 overflow-visible text-textmuted rtl:rotate-180"></i>
                        </a> </li>
                    
                    <li class="text-[12px]"> <a class="flex items-center text-textmuted"
                        href="javascript:void(0);">Operations 
                        </a> </li>
                    </ol>
                </nav>
            </div>

    
        </div>
        <!-- Page Header Close -->

            <div
                class=" !-mt-2 !p-0  bg-white  !border-0 border-defaultborder  !m-0">
              
                    <div class="menu-header-content bg-primary text-white">
                        <div class="flex items-center justify-between">
                            <h6 class="mb-0 text-[.9375rem] font-semibold text-white">Notifiche</h6>
                        </div>
                        {{-- <p class="dropdown-title-text subtext mb-0 text-white opacity-[0.6] pb-0 text-[0.75rem] ">You have 6
                            unread Notifications</p> --}}
                    </div>
                    <div class="dropdown-divider"></div>
                    
                    <ul class="list-none mb-0">
                        @foreach($operations as $operation)
            
                            <a class="cursor-pointer" href="{{ $operation->url }}">
                                
                                <li class="dropdown-item px-3 border-b">
                                    
                                    <div class="flex items-center my-3">
                                        
                                        <span class="avatar avatar-md me-2 avatar-rounded flex-shrink-0 bg-pinkmain">
                                        <i class="la la-file-alt text-[1.25rem]"></i>
                                        </span>
                                        <div class="ms-3">
                                        
                                            <h5 class="operation-label text-defaulttextcolor mb-1">{{ $operation->title }}</h5>
                                            <p class="operation-subtext text-[0.75rem]">{{ $operation->message }}</p>
                                        
                                            
                                        </div>
                              
                                        <div class="ms-auto operation-subtext">{{ $operation->created_at->diffForHumans() }}</div>
                                        
                                    </div>
                                </li>
                            </a>
                        @endforeach
                    
                    </ul>

                    <div class="mt-3 mb-3">
                        {{$operations->links()}}
                    </div>
                
            </div>

    </div>
</div> 
<!-- End::content  -->






@endsection

