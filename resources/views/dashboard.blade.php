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
                            <label for="image" class="block text-sm font-medium text-gray-700">Upload Image</label>
                            <input type="file" name="image" id="image" class="hidden" required>
                            <div id="imageContainer" class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center mb-6 relative">
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
                            </div>
                        </div>
                    </form>

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

                    <form action="#" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                            <input type="text" name="gender" id="gender" class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                        </div>

                        <div class="mb-4">
                            <label for="age" class="block text-sm font-medium text-gray-700">Age</label>
                            <input type="number" name="age" id="age" class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                        </div>

                        <div class="mb-4">
                            <label for="ethnicity" class="block text-sm font-medium text-gray-700">Ethnicity</label>
                            <select id="ethnicity" name="ethnicity" class="form-select rounded-md shadow-sm mt-1 block w-full" required>
                                <option value="1">African/Black</option>
                                <option value="2">Asian</option>
                                <option value="3">American</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="hair_color" class="block text-sm font-medium text-gray-700">Hair Color</label>
                            <select id="hair_color" name="hair_color" class="form-select rounded-md shadow-sm mt-1 block w-full" required>
                                <option value="1">Black</option>
                                <option value="2">Blonde</option>
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
                            <button type="submit" class="primary-btn">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>