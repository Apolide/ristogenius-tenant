@extends('layouts.app')

@section('content')


    <div class="content">
        <!-- Start::main-content -->
        <div class="main-content">
            <!-- Page Header -->
            <!-- Page Header -->
            <div class="md:flex block items-center justify-between mb-6 mt-[2rem]  page-header-breadcrumb">
                <div class="my-auto">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">Mail</h5>
                <nav>
                    <ol class="flex items-center whitespace-nowrap min-w-0">
                    <li class="text-[12px]"> <a class="flex items-center text-primary hover:text-primary"
                        href="javascript:void(0);"> Mail <i
                            class="ti ti-chevrons-right flex-shrink-0 mx-3 overflow-visible text-textmuted rtl:rotate-180"></i>
                        </a> </li>
                    <li class="text-[12px]"> <a class="flex items-center text-textmuted"
                        href="javascript:void(0);">Mail 
                        </a> </li>
                    </ol>
                </nav>
                </div>

                <div class="flex xl:my-auto right-content align-items-center">
                <div class="pe-1 xl:mb-0">
                    <button type="button" class="ti-btn ti-btn-info-full text-white ti-btn-icon me-2 btn-b !mb-0">
                    <i class="mdi mdi-filter-variant"></i>
                    </button>
                </div>
                <div class="pe-1 xl:mb-0">
                    <button type="button" class="ti-btn ti-btn-danger-full text-white ti-btn-icon me-2 !mb-0">
                    <i class="mdi mdi-star"></i>
                    </button>
                </div>
                <div class="pe-1 xl:mb-0">
                    <button type="button" class="ti-btn ti-btn-warning-full text-white  ti-btn-icon me-2 !mb-0">
                    <i class="mdi mdi-refresh"></i>
                    </button>
                </div>
                <div class="xl:mb-0">
                    <div class="hs-dropdown ti-dropdown">
                    <button class="ti-btn ti-btn-primary-full text-white dropdown-toggle !mb-0" type="button" id="dropdownMenuDate"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        14 Aug 2019 <i class="bi bi-chevron-down text-[.6rem] font-semibold"></i>
                    </button>
                    <ul class="hs-dropdown-menu ti-dropdown-menu hidden !z-[100]" aria-labelledby="dropdownMenuDate">
                        <li><a class="ti-dropdown-item" href="javascript:void(0);">2015</a></li>
                        <li><a class="ti-dropdown-item" href="javascript:void(0);">2016</a></li>
                        <li><a class="ti-dropdown-item" href="javascript:void(0);">2017</a></li>
                        <li><a class="ti-dropdown-item" href="javascript:void(0);">2018</a></li>
                    </ul>
                    </div>
                </div>
                </div>
            </div>
            <!-- Page Header Close -->
            <!-- Page Header Close -->
            <div class="container-fluid">
                <div class="main-mail-container !p-2 gap-x-2 flex">
                    <div class="mail-navigation border dark:border-defaultborder/10">
                        <div class="grid items-start !p-4 border-b dark:border-defaultborder/10">
                            <a href="javascript:void(0);" class="hs-dropdown-toggle ti-btn !bg-success text-white flex items-center justify-center !font-medium" data-hs-overlay="#todo-compose"><i class="ri-add-circle-line text-[1rem] align-middle"></i>Compose Mail
                            </a>
                            <div id="todo-compose" class="hs-overlay hidden size-full fixed top-0 start-0 z-[80] overflow-x-hidden overflow-y-auto pointer-events-none">
                                <div class="hs-overlay-open:mt-7 hs-overlay-open:opacity-100 hs-overlay-open:duration-500 mt-0 opacity-0 ease-out transition-all sm:max-w-[800px] sm:w-full m-3 sm:mx-auto">
                                    <div class="flex flex-col bg-white dark:bg-bodybg border border-defaultborder dark:border-defaultborder/10 shadow-sm rounded-sm pointer-events-auto">
                                    <div class="flex justify-between items-center py-3 px-4 border-b border-defaultborder dark:border-defaultborder/10">
                                        <h6 class="modal-title text-[1rem] font-semibold text-defaulttextcolor dark:text-defaulttextcolor/70" id="mail-ComposeLabel">Compose Mail</h6>
                                        <button type="button" class="flex justify-center items-center size-7 text-sm font-semibold rounded-full border border-transparent text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-gray-700 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600" data-hs-overlay="#todo-compose">
                                        <span class="sr-only">Close</span>
                                        <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="p-4 overflow-y-auto">
                                        <div class="grid grid-cols-12 gap-6">   
                                            <div class="xl:col-span-6 col-span-12 mb-2">
                                                <label for="fromMail" class="form-label">From<sup class="top-[0.5em]"><i class="ri-star-s-fill text-success text-[0.5rem]"></i></sup></label>
                                                <input type="email" class="form-control" id="fromMail" value="jsontaylor2345@gmail.com">
                                            </div>
                                            <div class="xl:col-span-6 col-span-12 mb-2">
                                                <label for="toMail" class="form-label">To<sup class="top-[0.5em]"><i class="ri-star-s-fill text-success text-[0.5rem]"></i></sup></label>
                                                <select class="form-control" name="toMail" id="toMail" multiple>
                                                    <option value="Choice 1" selected>jay@gmail.com</option>
                                                    <option value="Choice 2">kia@gmail.com</option>
                                                    <option value="Choice 3">don@gmail.com</option>
                                                    <option value="Choice 4">kimo@gmail.com</option>
                                                </select>
                                            </div>
                                            <div class="xl:col-span-6 col-span-12 mb-2">
                                                <label for="mailCC" class="form-label text-dark font-semibold">Cc</label>
                                                <input type="email" class="form-control" id="mailCC">
                                            </div>
                                            <div class="xl:col-span-6 col-span-12 mb-2">
                                                <label for="mailBcc" class="form-label text-dark font-semibold">Bcc</label>
                                                <input type="email" class="form-control" id="mailBcc">
                                            </div>
                                            <div class="xl:col-span-12 col-span-12 mb-2">
                                                <label for="Subject" class="form-label">Subject</label>
                                                <input type="text" class="form-control" id="Subject" placeholder="Subject">
                                            </div>
                                            <div class="xl:col-span-12 col-span-12">
                                                <label class="col-form-label">Content :</label>
                                                <div class="mail-compose">
                                                    <div id="mail-compose-editor"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-end items-center gap-x-2 py-3 px-4 border-t border-defaultborder dark:border-defaultborder/10">
                                        <button type="button" class="ti-btn  ti-btn-light" data-hs-overlay="#todo-compose">
                                        Close
                                        </button>
                                        <button type="button" class="ti-btn bg-primary text-white !font-medium">Send</button>
                                    </div>
                                    </div>
                                </div>
                                </div>
                        </div>
                        <div class="flex items-start !p-4 !bg-primary">
                            <div>
                                <span class="avatar avatar-md online avatar-rounded">
                                    <img src="../assets/images/faces/9.jpg" alt="">
                                </span>
                            </div>
                            <div class="ms-2">
                                <p class="font-semibold mb-0 text-white">Json Taylor</p>
                                <p class="text-white opacity-[0.7] text-[.6875rem] mb-0">jsontaylor1992@gmail.com</p>
                            </div>
                        </div>
                        <div class="hs-collapse-toggle p-2 inline-flex justify-center items-center gap-2 rounded-lg !border-0 font-medium bg-white dark:bg-bodybg text-gray-700 !shadow-none align-middle hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-blue-600 transition-all text-sm" data-hs-collapse="#navbar-collapse-basic" aria-controls="navbar-collapse-basic" aria-label="Toggle navigation">
                            <ul class="list-none mail-main-nav !border-0 !text-[0.813rem] overflow-scroll" id="mail-main-nav">
                                <li class="!px-0 !pt-0">
                                    <span class="text-[.6875rem] text-textmuted opacity-[0.7] font-semibold">MAILS</span>
                                </li>
                                <li class="active mail-type">
                                    <a href="javascript:void(0);">
                                        <div class="flex items-center">
                                            <span class="me-2 leading-none">
                                                <i class="ri-inbox-archive-line align-middle text-[.875rem]"></i>
                                            </span>
                                            <span class="flex-grow whitespace-nowrap">
                                                All Mails
                                            </span>
                                            <span class="badge bg-success/10 text-success !rounded-full">12,456</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="mail-type">
                                    <a href="javascript:void(0);">
                                        <div class="flex items-center">
                                            <span class="me-2 leading-none">
                                                <i class="ri-inbox-archive-line align-middle text-[.875rem]"></i>
                                            </span>
                                            <span class="flex-grow whitespace-nowrap">
                                                Inbox
                                            </span>
                                            <span class="badge bg-primary/10 text-primary !rounded-full">8</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="mail-type">
                                    <a href="javascript:void(0);">
                                        <div class="flex items-center">
                                            <span class="me-2 leading-none">
                                                <i class="ri-send-plane-2-line align-middle text-[.875rem]"></i>
                                            </span>
                                            <span class="flex-grow whitespace-nowrap">
                                                Sent
                                            </span>
                                        </div>
                                    </a>
                                </li>
                                <li class="mail-type">
                                    <a href="javascript:void(0);">
                                        <div class="flex items-center">
                                            <span class="me-2 leading-none">
                                                <i class="ri-draft-line align-middle text-[.875rem]"></i>
                                            </span>
                                            <span class="flex-grow whitespace-nowrap">
                                                Drafts
                                            </span>
                                        </div>
                                    </a>
                                </li>
                                <li class="mail-type">
                                    <a href="javascript:void(0);">
                                        <div class="flex items-center">
                                            <span class="me-2 leading-none">
                                                <i class="ri-spam-2-line align-middle text-[.875rem]"></i>
                                            </span>
                                            <span class="flex-grow whitespace-nowrap">
                                                Spam
                                            </span>
                                            <span class="badge bg-danger/10 text-danger !rounded-full">4</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="mail-type">
                                    <a href="javascript:void(0);">
                                        <div class="flex items-center">
                                            <span class="me-2 leading-none">
                                                <i class="ri-bookmark-line align-middle text-[.875rem]"></i>
                                            </span>
                                            <span class="flex-grow whitespace-nowrap">
                                                Important
                                            </span>
                                        </div>
                                    </a>
                                </li>
                                <li class="mail-type">
                                    <a href="javascript:void(0);">
                                        <div class="flex items-center">
                                            <span class="me-2 leading-none">
                                                <i class="ri-delete-bin-line align-middle text-[.875rem]"></i>
                                            </span>
                                            <span class="flex-grow whitespace-nowrap">
                                                Trash
                                            </span>
                                        </div>
                                    </a>
                                </li>
                                <li class="mail-type">
                                    <a href="javascript:void(0);">
                                        <div class="flex items-center">
                                            <span class="me-2 leading-none">
                                                <i class="ri-archive-line align-middle text-[.875rem]"></i>
                                            </span>
                                            <span class="flex-grow whitespace-nowrap">
                                                Archive
                                            </span>
                                        </div>
                                    </a>
                                </li>
                                <li class="mail-type">
                                    <a href="javascript:void(0);">
                                        <div class="flex items-center">
                                            <span class="me-2 leading-none">
                                                <i class="ri-star-line align-middle text-[.875rem]"></i>
                                            </span>
                                            <span class="flex-grow whitespace-nowrap">
                                                Starred
                                            </span>
                                            <span class="badge bg-warning/10 text-warning !rounded-full">12</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="px-0">
                                    <span class="text-[.6875rem] text-textmuted opacity-[0.7] font-semibold">SETTINGS</span>
                                </li>
                                <li>
                                    <a href="javascript:void(0);">
                                        <div class="flex items-center">
                                            <span class="me-2 leading-none">
                                                <i class="ri-settings-3-line align-middle text-[.875rem]"></i>
                                            </span>
                                            <span class="flex-grow whitespace-nowrap">
                                                Settings
                                            </span>
                                        </div>
                                    </a>
                                </li>
                                <li class="px-0">
                                    <span class="text-[.6875rem] text-textmuted opacity-[0.7] font-semibold">LABELS</span>
                                </li>
                                <li>
                                    <a href="javascript:void(0);">
                                        <div class="flex items-center">
                                            <span class="me-2 leading-none">
                                                <i
                                                    class="ri-price-tag-line align-middle text-[.875rem] font-semibold text-secondary"></i>
                                            </span>
                                            <span class="flex-grow whitespace-nowrap">
                                                Mail
                                            </span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);">
                                        <div class="flex items-center">
                                            <span class="me-2 leading-none">
                                                <i
                                                    class="ri-price-tag-line align-middle text-[.875rem] font-semibold text-danger"></i>
                                            </span>
                                            <span class="flex-grow whitespace-nowrap">
                                                Home
                                            </span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);">
                                        <div class="flex items-center">
                                            <span class="me-2 leading-none">
                                                <i
                                                    class="ri-price-tag-line align-middle text-[.875rem] font-semibold text-success"></i>
                                            </span>
                                            <span class="flex-grow whitespace-nowrap">
                                                Work
                                            </span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);">
                                        <div class="flex items-center">
                                            <span class="me-2 leading-none">
                                                <i
                                                    class="ri-price-tag-line align-middle text-[.875rem] font-semibold text-black dark:text-white"></i>
                                            </span>
                                            <span class="flex-grow whitespace-nowrap">
                                                Friends
                                            </span>
                                        </div>
                                    </a>
                                </li>
                                <li class="px-0">
                                    <span class="text-[.6875rem] text-textmuted opacity-[0.7] font-semibold">ONLINE USERS</span>
                                </li>
                                <li>
                                    <a href="javascript:void(0);">
                                        <div class="flex items-start leading-none">
                                            <div class="me-2">
                                                <span class="avatar avatar-sm online avatar-rounded">
                                                    <img src="../assets/images/faces/4.jpg" alt="">
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-defaulttextcolor font-semibold mb-1">Angelica</p>
                                                <p class="text-[0.75rem] text-textmuted mb-0">Hello this is angelica.</p>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);">
                                        <div class="flex items-start leading-none">
                                            <div class="me-2">
                                                <span class="avatar avatar-sm online avatar-rounded">
                                                    <img src="../assets/images/faces/6.jpg" alt="">
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-defaulttextcolor font-semibold mb-1">Rexha</p>
                                                <p class="text-[0.75rem] text-textmuted mb-0">Thanks for sharing file 😀.</p>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="total-mails border dark:border-defaultborder/10">
                        <div class="!p-4 flex items-center border-b dark:border-defaultborder/10">
                            <div class="me-4">
                                <input class="form-check-input" type="checkbox" id="checkAll" value="" aria-label="...">
                            </div>
                            <div class="flex-grow">
                                <h6 class="font-semibold mb-0 text-[1rem] !text-defaulttextcolor dark:!text-defaulttextcolor/70">All Mails</h6>
                            </div>
                            <button aria-label="button" type="button" class="ti-btn ti-btn-icon ti-btn-light me-1 lg:hidden block total-mails-close !mb-0">
                                <i class="ri-close-fill"></i>
                            </button>
                            <div class="hs-dropdown ti-dropdown  hidden">
                                <button aria-label="button" class="!w-[2.313rem] !h-[2.313rem]  rounded-sm ti-btn-icon ti-btn-light" type="button"
                                    aria-expanded="false">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                    <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium" href="javascript:void(0);">Recent</a></li>
                                    <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium" href="javascript:void(0);">Unread</a></li>
                                    <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium" href="javascript:void(0);">Mark All Read</a></li>
                                    <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium" href="javascript:void(0);">Spam</a></li>
                                    <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium" href="javascript:void(0);">Delete All</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="input-group">
                                <input type="text" class="form-control !bg-light !border-0 !rounded-s-md" placeholder="Search Email" aria-describedby="button-addon2">
                                <button aria-label="button" class="ti-btn ti-btn-light !rounded-s-none !mb-0" type="button" id="button-addon2"><i class="ri-search-line text-textmuted"></i></button>
                            </div>
                        </div>
                        <div class="mail-messages" id="mail-messages">
                            <ul class="list-none mb-0 mail-messages-container text-defaulttextcolor text-defaultsize">
                                <li class="!border-x-0">
                                    <div class="flex items-start">
                                        <div class="me-4 mt-1">
                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel1" value="" aria-label="...">
                                        </div>
                                        <div class="me-1 leading-none">
                                            <span
                                                class="avatar avatar-md online me-2 avatar-rounded mail-msg-avatar">
                                                <img src="../assets/images/faces/5.jpg" alt="">
                                            </span>
                                        </div>
                                        <div class="flex-grow">
                                            <a href="javascript:void(0);">
                                                <p class="mb-1 text-[0.75rem]">
                                                    Sujika <span
                                                        class="ltr:float-right rtl:float-left text-textmuted font-normal text-[.6875rem]"><span class="me-2"><i class="ri-attachment-2 align-middle text-[0.75rem]"></i></span>1:32PM</span>
                                                </p>
                                            </a>
                                            <p class="mail-msg mb-0">
                                                <span class="block mb-0  ">New Project details</span>
                                                <span
                                                    class="text-[.6875rem] text-textmuted text-wrap ">Lorem
                                                    ipsum dolor sit amet consectetur adipisicing elit
                                                    <button aria-label="button" type="button" class="btn p-0 leading-none mail-starred true border-0 ltr:float-right rtl:float-left">
                                                        <i class="ri-star-fill text-[.875rem]"></i>
                                                    </button>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </li>
                                <li class="active !border-t-0 !border-x-0">
                                    <div class="flex items-start">
                                        <div class="me-4 mt-1">
                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel2" value="" aria-label="..." checked>
                                        </div>
                                        <div class="me-1 leading-none">
                                            <span
                                                class="avatar avatar-md online me-2 avatar-rounded mail-msg-avatar">
                                                <img src="../assets/images/faces/12.jpg" alt="">
                                            </span>
                                        </div>
                                        <div class="flex-grow">
                                            <a href="javascript:void(0);">
                                                <p class="mb-1 text-[0.75rem] font-semibold">
                                                    Michael Jeremy <span
                                                        class="ltr:float-right rtl:float-left text-textmuted font-normal text-[.6875rem]">10:27AM</span>
                                                </p>
                                            </a>
                                            <p class="mail-msg mb-0">
                                                <span class="block mb-0  font-semibold  dark:!text-defaulttextcolor/70">History of planets are discovered yesterday.</span>
                                                <span
                                                    class="text-[.6875rem] text-[#8c9097] text-wrap dark:!text-defaulttextcolor/70">Lorem
                                                    ipsum dolor sit amet consectetur adipisicing elit
                                                    <button aria-label="button" type="button" class="btn p-0 leading-none mail-starred border-0 ltr:float-right rtl:float-left">
                                                        <i class="ri-star-fill text-[.875rem]"></i>
                                                    </button>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </li>
                                <li class="!border-t-0 !border-x-0">
                                    <div class="flex items-start">
                                        <div class="me-3 mt-1">
                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel3" value="" aria-label="...">
                                        </div>
                                        <div class="me-1 leading-none">
                                            <span
                                                class="avatar avatar-md offline me-2 avatar-rounded mail-msg-avatar">
                                                <img src="../assets/images/faces/14.jpg" alt="">
                                            </span>
                                        </div>
                                        <div class="flex-grow">
                                            <a href="javascript:void(0);">
                                                <p class="mb-1 text-[0.75rem]">
                                                    King Berunda <span class="badge bg-success text-white">Promotion</span><span
                                                        class="ltr:float-right rtl:float-left text-textmuted font-normal text-[.6875rem]">Yesterday, 10:27AM</span>
                                                </p>
                                            </a>
                                            <p class="mail-msg mb-0">
                                                <span class="block mb-0  ">Request for return of item ?</span>
                                                <span
                                                    class="text-[.6875rem] text-textmuted text-wrap ">Lorem
                                                    ipsum dolor sit amet consectetur adipisicing elit
                                                    <button aria-label="button" type="button" class="btn p-0 leading-none mail-starred border-0 ltr:float-right rtl:float-left">
                                                        <i class="ri-star-fill text-[.875rem]"></i>
                                                    </button>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </li>
                                <li class="!border-t-0 !border-x-0">
                                    <div class="flex items-start">
                                        <div class="me-3 mt-1">
                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel54" value="" aria-label="..." checked>
                                        </div>
                                        <div class="me-1 leading-none">
                                            <span
                                                class="avatar avatar-md offline me-2 avatar-rounded mail-msg-avatar">
                                                <img src="../assets/images/faces/7.jpg" alt="">
                                            </span>
                                        </div>
                                        <div class="flex-grow">
                                            <a href="javascript:void(0);">
                                                <p class="mb-1 text-[0.75rem]">
                                                    Samantha May <span class="badge bg-primary text-white">Social</span><span
                                                        class="ltr:float-right rtl:float-left text-textmuted font-normal text-[.6875rem]">24 Oct, 5:45PM</span>
                                                </p>
                                            </a>
                                            <p class="mail-msg mb-0">
                                                <span class="block mb-0 ">Completion of work is pending</span>
                                                <span
                                                    class="text-[.6875rem] text-textmuted text-wrap ">Lorem
                                                    ipsum dolor sit amet consectetur adipisicing elit
                                                    <button type="button" aria-label="button" class="btn p-0 leading-none mail-starred border-0 ltr:float-right rtl:float-left">
                                                        <i class="ri-star-fill text-[.875rem]"></i>
                                                    </button>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </li>
                                <li class="!border-t-0 !border-x-0">
                                    <div class="flex items-start">
                                        <div class="me-3 mt-1">
                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel14" value="" aria-label="...">
                                        </div>
                                        <div class="me-1 leading-none">
                                            <span
                                                class="avatar avatar-md offline me-2 avatar-rounded mail-msg-avatar">
                                                <img src="../assets/images/faces/15.jpg" alt="">
                                            </span>
                                        </div>
                                        <div class="flex-grow">
                                            <a href="javascript:void(0);">
                                                <p class="mb-1 text-[0.75rem]">
                                                    Alex Carey <span
                                                        class="ltr:float-right rtl:float-left text-textmuted font-normal text-[.6875rem]">12 Sep, 11:24AM</span>
                                                </p>
                                            </a>
                                            <p class="mail-msg mb-0">
                                                <span class="block mb-0  ">Most Probable date of project completion</span>
                                                <span
                                                    class="text-[.6875rem] text-textmuted text-wrap ">Lorem
                                                    ipsum dolor sit amet consectetur adipisicing elit
                                                    <button aria-label="button" type="button" class="btn p-0 leading-none mail-starred true border-0 ltr:float-right rtl:float-left">
                                                        <i class="ri-star-fill text-[.875rem]"></i>
                                                    </button>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </li>
                                <li class="!border-t-0 !border-x-0">
                                    <div class="flex items-start">
                                        <div class="me-3 mt-1">
                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel24" value="" aria-label="...">
                                        </div>
                                        <div class="me-1 leading-none">
                                            <span
                                                class="avatar avatar-md offline me-2 avatar-rounded mail-msg-avatar">
                                                <img src="../assets/images/faces/16.jpg" alt="">
                                            </span>
                                        </div>
                                        <div class="flex-grow">
                                            <a href="javascript:void(0);">
                                                <p class="mb-1 text-[0.75rem]">
                                                    Dawn Bradshaw <span class="badge bg-danger text-white">Personal</span><span
                                                        class="ltr:float-right rtl:float-left text-textmuted font-normal text-[.6875rem]">11 Sep, 10:32AM</span>
                                                </p>
                                            </a>
                                            <p class="mail-msg mb-0">
                                                <span class="block mb-0  ">Personal Mail</span>
                                                <span
                                                    class="text-[.6875rem] text-textmuted text-wrap ">Lorem
                                                    ipsum dolor sit amet consectetur adipisicing elit
                                                    <button aria-label="button" type="button" class="btn p-0 leading-none mail-starred true border-0 ltr:float-right rtl:float-left">
                                                        <i class="ri-star-fill text-[.875rem]"></i>
                                                    </button>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </li>
                                <li class="!border-t-0 !border-x-0">
                                    <div class="flex items-start">
                                        <div class="me-3 mt-1">
                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel34" value="" aria-label="...">
                                        </div>
                                        <div class="me-1 leading-none">
                                            <span
                                                class="avatar avatar-md offline me-2 avatar-rounded mail-msg-avatar">
                                                <img src="../assets/images/faces/3.jpg" alt="">
                                            </span>
                                        </div>
                                        <div class="flex-grow">
                                            <a href="javascript:void(0);">
                                                <p class="mb-1 text-[0.75rem]">
                                                    Kiara Jah <span
                                                        class="ltr:float-right rtl:float-left text-textmuted font-normal text-[.6875rem]">08 Aug, 08:12AM</span>
                                                </p>
                                            </a>
                                            <p class="mail-msg mb-0">
                                                <span class="block mb-0  ">Applying for bank loan?</span>
                                                <span
                                                    class="text-[.6875rem] text-textmuted text-wrap ">Lorem
                                                    ipsum dolor sit amet consectetur adipisicing elit
                                                    <button type="button" aria-label="button" class="btn p-0 leading-none mail-starred border-0 ltr:float-right rtl:float-left">
                                                        <i class="ri-star-fill text-[.875rem]"></i>
                                                    </button>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </li>
                                <li class="!border-t-0 !border-x-0">
                                    <div class="flex items-start">
                                        <div class="me-3 mt-1">
                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel44" value="" aria-label="...">
                                        </div>
                                        <div class="me-1 leading-none">
                                            <span
                                                class="avatar avatar-md offline me-2 avatar-rounded mail-msg-avatar">
                                                <img src="../assets/images/faces/13.jpg" alt="">
                                            </span>
                                        </div>
                                        <div class="flex-grow">
                                            <a href="javascript:void(0);">
                                                <p class="mb-1 text-[0.75rem]">
                                                    David Subhman <span
                                                        class="ltr:float-right rtl:float-left text-textmuted font-normal text-[.6875rem]">16 July, 05:52PM</span>
                                                </p>
                                            </a>
                                            <p class="mail-msg mb-0">
                                                <span class="block mb-0  ">Life Insurance Corparation.</span>
                                                <span
                                                    class="text-[.6875rem] text-textmuted text-wrap ">Lorem
                                                    ipsum dolor sit amet consectetur adipisicing elit
                                                    <button type="button" aria-label="button" class="tt-btn p-0 leading-none mail-starred border-0 ltr:float-right rtl:float-left">
                                                        <i class="ri-star-fill text-[.875rem]"></i>
                                                    </button>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="mails-information border dark:border-defaultborder/10 text-defaulttextcolor text-defaultsize">
                        <div class="mail-info-header flex flex-wrap gap-2 items-center">
                            <div class="me-1">
                                <span class="avatar avatar-md online me-2 avatar-rounded mail-msg-avatar">
                                    <img src="../assets/images/faces/12.jpg" alt="">
                                </span>
                            </div>
                            <div class="flex-grow">
                                <h6 class="mb-0 font-semibold text-[1rem] dark:text-defaulttextcolor/70">Michael Jeremy</h6>
                                <span class="text-textmuted text-[0.75rem]">michaeljeremy2194@gmail.com</span>
                            </div>
                            <div class="mail-action-icons">
                                <div class="hs-tooltip ti-main-tooltip">
                                    <button type="button" class="hs-tooltip-toggle ti-btn ti-btn-icon ti-btn-light">
                                        <i class="ri-star-line"></i>
                                        <span
                                            class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                            role="tooltip">
                                            Starred
                                        </span>
                                    </button>
                                </div>
                                <div class="hs-tooltip ti-main-tooltip">
                                    <button type="button" class="hs-tooltip-toggle ti-btn ti-btn-icon ti-btn-light">
                                        <i class="ri-inbox-archive-line"></i>
                                        <span
                                            class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                            role="tooltip">
                                            Archive
                                        </span>
                                    </button>
                                </div>
                                <div class="hs-tooltip ti-main-tooltip">
                                    <button type="button" class="hs-tooltip-toggle ti-btn ti-btn-icon ti-btn-light">
                                        <i class="ri-spam-2-line"></i>
                                        <span
                                            class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                            role="tooltip">
                                            Spam
                                        </span>
                                    </button>
                                </div>
                                <div class="hs-tooltip ti-main-tooltip">
                                    <button type="button" class="hs-tooltip-toggle ti-btn ti-btn-icon ti-btn-light">
                                        <i class="ri-delete-bin-line"></i>
                                        <span
                                            class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                            role="tooltip">
                                            Delete
                                        </span>
                                    </button>
                                </div>
                                <div class="hs-tooltip ti-main-tooltip">
                                    <button type="button" class="hs-tooltip-toggle ti-btn ti-btn-icon ti-btn-light">
                                        <i class="ri-reply-line"></i>
                                        <span
                                            class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                            role="tooltip">
                                            Reply
                                        </span>
                                    </button>
                                </div>
                            </div>
                            <div class="responsive-mail-action-icons">
                                <div class="hs-dropdown ti-dropdown  hidden">
                                    <button aria-label="button" class="!w-[2.313rem] !h-[2.313rem]  rounded-sm ti-btn-icon ti-btn-light" type="button" aria-expanded="false">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                        <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium" href="javascript:void(0);"><i class="inline-flex ri-star-line me-1 align-middle"></i>Starred</a></li>
                                        <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium" href="javascript:void(0);"><i class="inline-flex ri-inbox-archive-line me-1 align-middle"></i>Archive</a></li>
                                        <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium" href="javascript:void(0);"><i class="inline-flex ri-spam-2-line me-1 align-middle"></i>Report Spam</a></li>
                                        <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium" href="javascript:void(0);"><i class="inline-flex ri-delete-bin-line me-1 align-middle"></i>Delete</a></li>
                                        <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium" href="javascript:void(0);"><i class="inline-flex ri-reply-line me-1 align-middle"></i>Reply</a></li>
                                    </ul>
                                </div>
                                <button aria-label="button" type="button" class="ti-btn ti-btn-icon ti-btn-light ms-1">
                                    <i class="ri-close-fill"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mail-info-body  dark:!border-defaultborder/10 p-6" id="mail-info-body">
                            <div class="sm:flex block items-center justify-between mb-6">
                                <div>
                                    <p class="text-[1.25rem] font-semibold mb-0">History of planets are discovered yesterday.</p>
                                </div>
                                <div class="ltr:float-right rtl:float-left">
                                    <span class="me-2 text-[0.75rem] text-textmuted">Oct-22-2022,03:05PM</span>
                                </div>
                            </div>
                            <div class="main-mail-content mb-6">
                                <p class="text-[.875rem] font-semibold mb-4">Hi, Json Taylor Greetings 🖐</p>
                                <p class="mb-2 text-[0.75rem] text-textmuted">Earth, our home, is the third planet from the sun. While scientists continue to hunt for clues of life beyond Earth, our home planet remains the only place in the universe where we've ever identified living organisms. .</p>
                                <p class="mb-2 text-[0.75rem] text-textmuted">Earth has a diameter of roughly 8,000 miles (13,000 kilometers) and is mostly round because gravity generally pulls matter into a ball. But the spin of our home planet causes it to be squashed at its poles and swollen at the equator, making the true shape of the Earth an "oblate spheroid.".</p>
                                <p class="mb-0 mt-4">
                                    <span class="block">Regards,</span>
                                    <span class="block">Michael Jeremy</span>
                                </p>
                            </div>
                            <div class="mail-attachments mb-6">
                                <div class="flex justify-between items-center">
                                    <div class="mb-0">
                                        <span class="text-[.875rem] font-semibold dark:!text-defaulttextcolor/70"><i class="ri-attachment-2 me-1 align-middle"></i>Attachments (1.8mb):</span>
                                    </div>
                                    <div>
                                        <button type="button" class="ti-btn !py-1 !px-2 !text-[0.75rem] !font-medium ti-btn-success">Download All</button>
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center flex-wrap">
                                    <a href="javascript:void(0);" class="mail-attachment border dark:border-defaultborder/10 mb-1 me-2">
                                        <div class="attachment-icon"><svg xmlns="http://www.w3.org/2000/svg" baseProfile="tiny" viewBox="0 0 512 512"><path fill="#FFF" d="M422.3 477.9c0 7.6-6.2 13.8-13.8 13.8h-305c-7.6 0-13.8-6.2-13.8-13.8V34.1c0-7.6 6.2-13.8 13.8-13.8h230.1V109h88.7v368.9z"/><path fill="#2B669F" d="M333.6 6H103.5C88 6 75.4 18.6 75.4 34.1v443.8c0 15.5 12.6 28.1 28.1 28.1h305c15.5 0 28.1-12.6 28.1-28.1V109.1L333.6 6zm88.7 471.9c0 7.6-6.2 13.8-13.8 13.8h-305c-7.6 0-13.8-6.2-13.8-13.8V34.1c0-7.6 6.2-13.8 13.8-13.8h230.1V109h88.7v368.9z"/><path fill="#084272" d="M333.6 6v103.1h103z"/><g><path fill="#084272" d="M465.9 450.8H46.1V308c0-9.8 7.9-17.7 17.7-17.7h384.3c9.8 0 17.7 7.9 17.7 17.7v142.8z"/><path fill="#1A252D" d="M436.6 450.8v19.5l29.3-19.5zM75.4 450.8v19.5l-29.3-19.5z"/><path fill="#2B669F" d="M64.1 308.4h383.7v124.5H64.1z"/></g><g fill="#2B669F"><path d="M298.3 78.6h-177a6.7 6.7 0 010-13.4h177a6.7 6.7 0 010 13.4zM298.3 110.6h-177a6.7 6.7 0 010-13.4h177a6.7 6.7 0 010 13.4zM391.8 142.5H121.3a6.7 6.7 0 010-13.4h270.5a6.7 6.7 0 010 13.4zM391.8 174.5H121.3a6.7 6.7 0 010-13.4h270.5a6.7 6.7 0 010 13.4zM391.8 206.5H121.3a6.7 6.7 0 010-13.4h270.5a6.7 6.7 0 010 13.4zM391.8 238.4H121.3a6.7 6.7 0 010-13.4h270.5a6.7 6.7 0 010 13.4zM391.8 270.4H121.3a6.7 6.7 0 010-13.4h270.5a6.7 6.7 0 010 13.4z"/></g><g fill="#FFF"><path d="M229.3 373.3c0 6.9-1.6 12.5-4.7 16.7-3.1 4.2-7.5 6.3-13.2 6.3-2.2 0-4.2-.4-5.9-1.3-1.7-.9-3.2-2.1-4.5-3.7v21.8h-14.4v-63.8h13.6l.4 5c1.3-1.9 2.8-3.3 4.6-4.3 1.8-1 3.8-1.5 6.1-1.5 5.7 0 10.1 2.2 13.3 6.6 3.1 4.4 4.7 10.2 4.7 17.4v.8zm-14.3-.9c0-3.9-.6-7-1.7-9.4-1.1-2.4-3-3.5-5.4-3.5-1.6 0-3 .3-4.1.9-1.1.6-2 1.5-2.7 2.6v19.2c.7 1 1.6 1.7 2.7 2.2 1.1.5 2.5.7 4.1.7 2.5 0 4.3-1 5.4-3.1 1.1-2.1 1.6-5 1.6-8.7v-.9zM239.8 372.4c0-7.2 1.6-13 4.7-17.4 3.1-4.4 7.6-6.6 13.3-6.6 2.1 0 4 .5 5.8 1.5 1.7 1 3.3 2.4 4.6 4.2V329h14.4v66.4H270l-1-5.6c-1.4 2.1-3 3.7-4.9 4.8-1.9 1.1-4 1.7-6.4 1.7-5.7 0-10.1-2.1-13.2-6.3-3.1-4.2-4.7-9.7-4.7-16.6v-1zm14.4.9c0 3.7.5 6.7 1.6 8.7 1.1 2.1 2.9 3.1 5.5 3.1 1.5 0 2.8-.3 4-.8 1.1-.6 2.1-1.4 2.8-2.4v-18.6c-.7-1.2-1.7-2.2-2.8-2.8-1.1-.7-2.4-1-3.9-1-2.6 0-4.4 1.2-5.5 3.5-1.1 2.4-1.7 5.5-1.7 9.4v.9zM300 395.4v-36.1h-6.6v-10h6.6v-4.8c0-5.3 1.6-9.3 4.8-12.2 3.2-2.9 7.7-4.3 13.5-4.3 1.1 0 2.2.1 3.3.2 1.1.2 2.4.4 3.8.7l-1.1 10.6c-.8-.1-1.5-.2-2.1-.3-.6-.1-1.3-.1-2.2-.1-1.8 0-3.2.5-4.2 1.4-1 .9-1.4 2.3-1.4 4v4.8h9.1v10h-9.1v36.1H300z"/></g></svg>
                                        </div>
                                        <div class="leading-none">
                                            <p class="mb-1 attachment-name truncate dark:border-defaultborder/10">
                                                Earth_Archeology_2.21-4.pdf
                                            </p>
                                            <p class="mb-0 text-[.6875rem] text-textmuted">
                                                0.85MB
                                            </p>
                                        </div>
                                    </a>
                                    <a href="javascript:void(0);" class="mail-attachment mb-1 border dark:border-defaultborder/10">
                                        <div class="attachment-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="#FFF" d="M422.3 477.9c0 7.6-6.2 13.8-13.8 13.8h-305c-7.6 0-13.8-6.2-13.8-13.8V34.1c0-7.6 6.2-13.8 13.8-13.8h230.1V109h88.7v368.9z"/><path fill="#24A885" d="M333.6 6H103.5C88 6 75.4 18.6 75.4 34.1v443.8c0 15.5 12.6 28.1 27.1 28.1h305c16.5 0 29.1-12.6 29.1-28.1V109.1L333.6 6zm88.7 471.9c0 7.6-6.2 13.8-13.8 13.8h-305c-7.6 0-13.8-6.2-13.8-13.8V34.1c0-7.6 6.2-13.8 13.8-13.8h230.1V109h88.7v368.9z"/><path fill="#16876B" d="M333.6 6v103.1h103zM465.9 450.8H46.1V308c0-9.8 7.9-17.7 17.7-17.7h384.3c9.8 0 17.7 7.9 17.7 17.7v142.8z"/><path fill="#59E0B9" d="M436.6 450.8v19.5l29.3-19.5zM75.4 450.8v19.5l-29.3-19.5z"/><path fill="#2EBA98" d="M64.1 308.4h383.7v124.5H64.1z"/><path fill="#16876B" d="M298.3 78.6h-177a6.7 6.7 0 0 1 0-13.4h177a6.7 6.7 0 0 1 0 13.4zM298.3 110.6h-177a6.7 6.7 0 0 1 0-13.4h177a6.7 6.7 0 0 1 0 13.4zM391.8 142.5H121.3a6.7 6.7 0 0 1 0-13.4h270.5a6.7 6.7 0 0 1 0 13.4zM391.8 174.5H121.3a6.7 6.7 0 0 1 0-13.4h270.5a6.7 6.7 0 0 1 0 13.4zM391.8 206.5H121.3a6.7 6.7 0 0 1 0-13.4h270.5a6.7 6.7 0 0 1 0 13.4zM391.8 238.4H121.3a6.7 6.7 0 0 1 0-13.4h270.5a6.7 6.7 0 0 1 0 13.4zM391.8 270.4H121.3a6.7 6.7 0 0 1 0-13.4h270.5a6.7 6.7 0 0 1 0 13.4z"/><path fill="#FFF" d="M191.3 349.7v43.9c0 5.4-1.4 9.6-4.3 12.5-2.9 2.9-6.9 4.4-12.1 4.4-1.2 0-2.2-.1-3.2-.2s-2-.3-3.1-.5l.6-10.2c.8.2 1.4.3 2 .4s1.2.1 2.1.1c1.5 0 2.6-.5 3.4-1.6.8-1.1 1.2-2.7 1.2-4.8v-43.9h13.4zm-.2-10h-13.6v-9.1h13.6v9.1zM244.8 372.3c0 6.5-1.5 11.7-4.4 15.7-2.9 3.9-7.1 5.9-12.4 5.9-2.1 0-3.9-.4-5.5-1.2-1.6-.8-3-2-4.2-3.5v20.5h-13.5v-60h12.8l.4 4.7c1.2-1.8 2.6-3.1 4.3-4.1 1.7-1 3.6-1.4 5.7-1.4 5.4 0 9.5 2.1 12.5 6.2s4.4 9.6 4.4 16.3v.9zm-13.5-.8c0-3.7-.5-6.6-1.6-8.8-1.1-2.2-2.8-3.3-5.1-3.3-1.5 0-2.8.3-3.9.8-1.1.6-1.9 1.4-2.5 2.4v18.1c.6.9 1.5 1.6 2.5 2.1 1.1.5 2.4.7 3.9.7 2.4 0 4.1-1 5.1-2.9 1-2 1.5-4.7 1.5-8.2v-.9zM275.7 393.9c-6.6 0-11.7-2-15.4-6-3.7-4-5.6-9-5.6-15.1v-1.5c0-6.6 1.8-12 5.3-16.2 3.5-4.2 8.6-6.2 15.2-6.2 5.8 0 10.4 1.7 13.6 5.2 3.2 3.5 4.8 8.3 4.8 14.4v7.1h-24.8l-.1.2c.2 2.3 1.1 4.1 2.5 5.5 1.5 1.4 3.6 2.1 6.3 2.1 2.6 0 4.7-.2 6.5-.6 1.7-.4 3.7-1.1 5.9-2l3.2 8.3c-1.9 1.4-4.4 2.5-7.5 3.4-3.1 1-6.4 1.4-9.9 1.4zm-.5-34.5c-2.2 0-3.8.7-4.8 2.1-1 1.4-1.6 3.2-1.8 5.6l.1.2h11.7v-1c0-2.2-.4-3.9-1.2-5.1-.7-1.3-2.1-1.8-4-1.8zM303.3 371.5c0-6.8 1.5-12.2 4.4-16.3 3-4.1 7.1-6.2 12.5-6.2 2.3 0 4.3.5 6 1.6 1.7 1.1 3.2 2.6 4.5 4.6l.9-5.4h11.9v42.9c0 5.7-2 10.1-5.9 13.3-3.9 3.1-9.3 4.7-16.2 4.7-2.2 0-4.6-.3-7.1-.9-2.5-.6-4.8-1.4-7.1-2.5l2.3-10c1.9.9 3.8 1.5 5.6 1.9 1.8.4 3.8.6 6.1.6 2.9 0 5-.6 6.4-1.7 1.4-1.1 2.1-3 2.1-5.4v-3.4c-1.2 1.6-2.7 2.8-4.3 3.6-1.6.8-3.5 1.2-5.5 1.2-5.3 0-9.5-2-12.4-5.9-2.9-4-4.4-9.2-4.4-15.7v-1zm13.5.8c0 3.5.5 6.3 1.5 8.2 1 2 2.8 2.9 5.2 2.9 1.5 0 2.7-.2 3.8-.7 1.1-.5 1.9-1.1 2.6-2v-18.2c-.7-1-1.5-1.8-2.6-2.4-1-.5-2.3-.8-3.7-.8-2.4 0-4.1 1.1-5.2 3.3-1.1 2.2-1.6 5.2-1.6 8.8v.9z"/></svg>
                                        </div>
                                        <div class="leading-none">
                                            <p class="mb-1 attachment-name truncate dark:border-defaultborder/10">
                                                Planets_Image.Jpeg
                                            </p>
                                            <p class="mb-0 text-[.6875rem] text-textmuted">
                                                457KB
                                            </p>
                                        </div>
                                    </a>
                                    <div class="hs-tooltip ti-main-tooltip">
                                        <a aria-label="anchor" href="javascript:void(0);" class="!w-[2.8rem] !h-[2.8rem] ti-btn-icon ti-btn-outline-light dark:hover:!bg-light  flex items-center justify-center !rounded-md dark:!border-defaultborder/10 !ms-2 !text-[1.2rem] !text-defaulttextcolor dark:!text-defaulttextcolor/70 border">
                                            <i class="ri-attachment-2"></i>
                                            <span
                                                class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                                role="tooltip">
                                                Image_012.jpeg
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <span class="text-[.875rem] font-semibold"><i class="ri-reply-all-line me-1 align-middle inline-block"></i>Reply:</span>
                            </div>
                            <div class="mail-reply dark:border-defaultborder/10">
                                <div id="mail-reply-editor"></div>
                            </div>
                        </div>
                        <div class="mail-info-footer  dark:!border-defaultborder/10 flex flex-wrap gap-2 items-center justify-between">
                            <div>
                                <div class="hs-tooltip ti-main-tooltip">
                                    <button type="button" class="hs-tooltip-toggle ti-btn ti-btn-icon ti-btn-light">
                                        <i class="ri-printer-line"></i>
                                        <span
                                            class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                            role="tooltip">
                                            Print
                                        </span>
                                    </button>
                                </div>
                                <div class="hs-tooltip ti-main-tooltip">
                                    <button type="button" class="hs-tooltip-toggle ti-btn ti-btn-icon ti-btn-light">
                                        <i class="ri-mail-open-line"></i>
                                        <span
                                            class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                            role="tooltip">
                                            Mark as read
                                        </span>
                                    </button>
                                </div>
                                <div class="hs-tooltip ti-main-tooltip">
                                    <button type="button" class="hs-tooltip-toggle ti-btn ti-btn-icon ti-btn-light">
                                        <i class="ri-refresh-line"></i>
                                        <span
                                            class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700"
                                            role="tooltip">
                                            Reload
                                        </span>
                                    </button>
                                </div>
                            </div>
                            <div class="space-x-2 rtl:space-x-reverse">
                                <button type="button" class="ti-btn ti-btn-secondary-full">
                                    <i class="ri-share-forward-line me-1 inline-block align-middle"></i>Forward
                                </button>
                                <button type="button" class="ti-btn ti-btn-danger-full ms-1">
                                    <i class="ri-reply-all-line me-1 inline-block align-middle"></i>Reply
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-17709737813"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());

    gtag('config', 'AW-17709737813');
</script>

@endsection