<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Circular container for uploading selfie -->
                    <form id="imageUploadForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <input type="file" name="image" id="image" class="hidden" required>
                            <div id="imageContainer" class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center mb-6 relative">
                                <!-- Image displayed if image_name is present -->
                                @if (Session::has('image_name'))
                                <img src="{{ Session::get('image_name') }}" alt="Uploaded Image" class="w-32 h-32 rounded-full object-cover" style="width: 150px; height: 150px;">
                                @else
                                <!-- Label to trigger file upload when clicked -->
                                <label for="image" class="cursor-pointer">
                                    <!-- SVG icon -->
                                    <svg id="uploadIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-16 h-16 text-gray-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </label>
                                <!-- Loader icon, hidden by default -->
                                <div id="loader" class="hidden absolute inset-0 flex items-center justify-center bg-gray-200 rounded-full">
                                    <svg class="w-16 h-16 animate-spin text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v4m0 0v4m0-4h4m0 0H8m4 4v4m0 0v4m0-4h4m0 0H8"></path>
                                    </svg>
                                </div>
                                @endif
                                <!-- Option to change selfie -->
                                @if (Session::has('image_name'))
                                <div class="absolute inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 opacity-0 hover:opacity-100 transition duration-300">
                                    <label for="image" class="cursor-pointer text-white font-medium">Change Selfie</label>
                                </div>
                                @endif
                            </div>
                        </div>
                    </form>

                    <form action="{{ route('generate_headshot') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                            <select id="ethnicity" name="gender" id="gender" class="form-select rounded-md shadow-sm mt-1 block w-full" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="age" class="block text-sm font-medium text-gray-700">Age</label>
                            <input type="number" name="age" id="age" class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                        </div>

                        <div class="mb-4">
                            <label for="ethnicity" class="block text-sm font-medium text-gray-700">Ethnicity</label>
                            <select id="ethnicity" name="ethnicity" class="form-select rounded-md shadow-sm mt-1 block w-full" required>
                                <option value="African/Black">African/Black</option>
                                <option value="Asian">Asian</option>
                                <option value="American">American</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="hair_color" class="block text-sm font-medium text-gray-700">Hair Color</label>
                            <select id="hair_color" name="hair_color" class="form-select rounded-md shadow-sm mt-1 block w-full" required>
                                <option value="Black">Black</option>
                                <option value="Blonde">Blonde</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="hair_length" class="block text-sm font-medium text-gray-700">Hair Length</label>
                            <select id="hair_length" name="hair_length" class="form-select rounded-md shadow-sm mt-1 block w-full" required>
                                <option value="short">Short</option>
                                <option value="medium">Medium</option>
                                <option value="long">Long</option>
                            </select>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Submit
                            </button>
                        </div>

                        <div class="flex justify-between sm:w-full lg:w-3/5 mx-auto">
                            <div class="mr-4">
                                @if (session('success'))
                                <div>
                                    <img src="{{ session('image_url1') }}" alt="Generated Headshot 1" class="rounded-full img-fluid w-36 h-36 sm:w-24 sm:h-24" onclick="handleImageClick('{{ session('image_url1') }}')">
                                </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                @if (session('success'))
                                <div>
                                    <img src="{{ session('image_url2') }}" alt="Generated Headshot 2" class="rounded-full img-fluid w-36 h-36 sm:w-24 sm:h-24" onclick="handleImageClick('{{ session('image_url2') }}')">
                                </div>
                                @endif
                            </div>
                        </div>



                        @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                        @endif
                    </form>

                    <div id="swappedImageContainer"></div>

                    <script>
                        document.getElementById('image').addEventListener('change', function() {
                            // Show the loader
                            document.getElementById('loader').classList.remove('hidden');

                            // Get the form element
                            var form = document.getElementById('imageUploadForm');
                            var formData = new FormData(form);

                            // Make an AJAX request to upload the image
                            fetch('{{ route("upload_image") }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                                    },
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    // Hide the loader
                                    document.getElementById('loader').classList.add('hidden');

                                    if (data.success) {
                                        // Remove the SVG icon
                                        document.getElementById('uploadIcon').remove();

                                        // Create an img element and set the src to the uploaded image URL
                                        var img = document.createElement('img');
                                        img.src = data.image_url;
                                        img.className = 'w-32 h-32 rounded-full object-cover';
                                        img.style = 'width: 150px; height: 150px;';

                                        // Append the img element to the container
                                        document.getElementById('imageContainer').appendChild(img);
                                    } else {
                                        alert('Image upload failed');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('An error occurred while uploading the image');
                                    // Hide the loader in case of error
                                    document.getElementById('loader').classList.add('hidden');
                                });
                        });
                    </script>

                    <script>
                        // Function to handle the click event on the images
                        function handleImageClick(imageUrl) {
                            var SwapImageUrl = document.getElementById('imageContainer').querySelector('img').src;

                            fetch('{{ route("face_swap") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        swap_image_url: SwapImageUrl,
                                        target_image_url: imageUrl
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.swapped_image_url) {
                                        var swappedImg = document.createElement('img');
                                        swappedImg.src = data.swapped_image_url;
                                        swappedImg.className = 'rounded-full img-fluid';

                                        document.getElementById('swappedImageContainer').appendChild(swappedImg);
                                    } else {
                                        alert('Face swap failed.');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('An error occurred while performing face swap.');
                                });
                        }
                    </script>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>