<div>

    <div class="box">
        <div class="box-header border-none">
            <div class="flex justify-between">
                <div>
                    <div class="box-title pb-0">ELENCO UTENTI</div>
                    <p class="text-xs text-gray-500 font-normal">Example of Valex Simple Table. <a href=""
                            class="text-black dark:text-white">Learn more</a></p>
                </div>

            </div>

        </div>


        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered whitespace-nowrap min-w-full">
                    <thead>
                        <tr class="!border-defaultborder dark:!border-defaultborder/10">
                            <th scope="col" class="border-b  dark:border-defaultborder/10 text-start">User
                                photo</th>
                            <th scope="col"
                                class="border border-defaultborder dark:border-defaultborder/10 text-start">
                                Nome</th>
                            <th scope="col"
                                class="border border-defaultborder dark:border-defaultborder/10 text-start">
                                Email</th>
                            <th scope="col"
                                class="border border-defaultborder dark:border-defaultborder/10 text-start">
                                Status</th>
                            <th scope="col"
                                class="border border-defaultborder dark:border-defaultborder/10 text-start">
                                Ruolo</th>
                            <th scope="col"
                                class="border border-defaultborder dark:border-defaultborder/10 text-start">
                                Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($users as $user)
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <td class="	whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200"
                                    class="	whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200">
                                    <img alt="avatar" class="!rounded-full avatar-md avatar"
                                        src="../assets/images/faces/1.jpg">
                                </td>
                                <td class="	whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{$user->name}}</td>
                                <td class="	whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{$user->email}}</td>
                                <td class="	whitespace-nowrap text-sm text-gray-800 dark:text-gray-200"
                                    class="	whitespace-nowrap text-sm text-gray-800 dark:text-gray-200"><span
                                        class="badge bg-primary/10 !text-primary">Active</span></td>
                                <td class="	whitespace-nowrap text-sm text-gray-800 dark:text-gray-200"
                                    class="	whitespace-nowrap text-sm text-gray-800 dark:text-gray-200"><a
                                        href="javascript:void(0);"> {{$user->getRoleNames()[0]}}</a></td>
                                <td class="	whitespace-nowrap text-sm font-medium">
                                    <div class="hs-tooltip ti-main-tooltip">
                                        <a href="javascript:void(0);"
                                            class="ti-btn ti-btn-sm hs-tooltip-toggle flex items-center justify-center gap-x-2 text-sm font-semibold rounded-sm border border/10 bg-primary text-white hover:bg-primary disabled:opacity-50 disabled:pointer-events-none dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                            <i class="las la-search"></i>
                                            <span
                                                class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                role="tooltip">
                                                Search
                                            </span>
                                        </a>
                                    </div>
                                    <div class="hs-tooltip ti-main-tooltip">
                                        <a href="/manage/users/edit/{{$user->id}}"
                                            class="ti-btn ti-btn-sm hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm border border/10 bg-info text-white hover:bg-info disabled:opacity-50 disabled:pointer-events-none dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                            <i class="las la-pen"></i>
                                            <span
                                                class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                role="tooltip">
                                                Edit
                                            </span>
                                        </a>
                                    </div>
                                    <div class="hs-tooltip ti-main-tooltip">
                                        <a href="javascript:void(0);"
                                            class="ti-btn ti-btn-sm hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm border border/10 bg-danger text-white hover:bg-danger disabled:opacity-50 disabled:pointer-events-none dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                            <i class="las la-trash"></i>
                                            <span
                                                class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                role="tooltip">
                                                Delete
                                            </span>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page  navigation">
            <ul class="ti-pagination !mb-4 justify-end">
                <li class="page-item disabled">
                    <a class="page-link px-3 py-[0.375rem]">Previous</a>
                </li>
                <li class="page-item active"><a class="page-link px-3 py-[0.375rem]"
                        href="javascript:void(0);">1</a></li>
                <li class="page-item"><a class="page-link px-3 py-[0.375rem]"
                        href="javascript:void(0);">2</a></li>
                <li class="page-item"><a class="page-link px-3 py-[0.375rem]"
                        href="javascript:void(0);">3</a></li>
                <li class="page-item">
                    <a class="page-link px-3 py-[0.375rem]" href="javascript:void(0);">Next</a>
                </li>
            </ul>
        </nav>
        <!-- End Pagination -->
    </div>


</div>


  
    

