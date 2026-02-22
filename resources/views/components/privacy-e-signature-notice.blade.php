<div id="privacyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-600 to-sky-600 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/sdodesignlogo.png') }}" alt="SDO Logo" class="h-12 w-12 object-contain">
                <h2 class="text-xl font-bold text-white">Data Privacy Notice and E-Signature Collection</h2>
            </div>
            <button type="button" onclick="closePrivacyPolicy()" class="text-white hover:text-gray-200 text-2xl font-bold">&times;</button>
        </div>

        <!-- Modal Content -->
        <div class="px-6 py-4">
            <p class="text-sm text-gray-600 mb-4">
                Welcome to the Attendance Monitoring System of DepEd SDO Balanga City. Your privacy is important to us, and we are committed to protecting your personal data in accordance with the Data Privacy Act of 2012 (RA 10173).
            </p>

            <h3 class="text-lg font-semibold text-gray-800 mb-2 mt-4">Collection and Use of Personal Data</h3>
            <p class="text-sm text-gray-700 mb-2">
                When you access our website, we may collect certain information to enhance your browsing experience and improve our services. This includes:
            </p>
            <ul class="list-disc list-inside text-sm text-gray-700 mb-4 space-y-1 ml-4">
                <li><strong>Automatically collected data:</strong> IP address, browser type, operating system, and browsing behavior.</li>
                <li><strong>Voluntarily provided data:</strong> Information you provide through forms (e.g., inquiries, feedback, or application submissions).</li>
            </ul>
            <p class="text-sm text-gray-700 mb-4">
                We ensure that your data will only be used for legitimate purposes such as responding to your inquiries, providing updates, or improving the services of DepEd SDO Balanga City.
            </p>

            <h3 class="text-lg font-semibold text-gray-800 mb-2 mt-4">Data Protection</h3>
            <p class="text-sm text-gray-700 mb-4">
                We implement appropriate technical and organizational measures to secure your personal data against unauthorized access, disclosure, or misuse. Your data will only be retained as long as necessary for its intended purposes, in compliance with applicable laws and regulations.
            </p>

            <h3 class="text-lg font-semibold text-gray-800 mb-2 mt-4">Sharing of Data</h3>
            <p class="text-sm text-gray-700 mb-4">
                Your personal data will not be shared with third parties except when required by law or with your explicit consent.
            </p>

            <h3 class="text-lg font-semibold text-gray-800 mb-2 mt-4">E-Signature Collection</h3>
            <p class="text-sm text-gray-700 mb-2">
                As part of the registration process, we collect your electronic signature to verify your attendance and participation in this seminar. Your signature serves as proof of your attendance and will be used solely for the following purposes:
            </p>
            <ul class="list-disc list-inside text-sm text-gray-700 mb-4 space-y-1 ml-4">
                <li>Verification of your attendance at this specific seminar</li>
                <li>Generation of official registration and attendance documents</li>
                <li>Compliance with DepEd and PRC requirements for CPD program documentation</li>
            </ul>

            <h4 class="text-base font-semibold text-gray-800 mb-2 mt-3">How We Collect Your Signature</h4>
            <p class="text-sm text-gray-700 mb-2">
                Your electronic signature is collected through a secure signature pad interface. The signature is captured as a digital image and immediately encrypted with security watermarks that include:
            </p>
            <ul class="list-disc list-inside text-sm text-gray-700 mb-4 space-y-1 ml-4">
                <li>Seminar identification information</li>
                <li>Date and time of signature capture</li>
                <li>Unique attendee identification</li>
                <li>Security verification markers to prevent tampering</li>
            </ul>

            <h4 class="text-base font-semibold text-gray-800 mb-2 mt-3">Security Measures</h4>
            <p class="text-sm text-gray-700 mb-2">
                We implement multiple layers of security to protect your signature:
            </p>
            <ul class="list-disc list-inside text-sm text-gray-700 mb-4 space-y-1 ml-4">
                <li><strong>Encryption:</strong> Your signature is encrypted using industry-standard encryption methods</li>
                <li><strong>Watermarking:</strong> Security watermarks are embedded directly into the signature image to prevent unauthorized modification</li>
                <li><strong>Hash Verification:</strong> Digital hash codes are generated to ensure signature integrity and detect any tampering attempts</li>
                <li><strong>Secure Storage:</strong> Signatures are stored in secure databases with restricted access</li>
                <li><strong>Access Controls:</strong> Only authorized personnel can access signature data for legitimate purposes</li>
            </ul>

            <h4 class="text-base font-semibold text-gray-800 mb-2 mt-3">Use of Your Signature</h4>
            <p class="text-sm text-gray-700 mb-4">
                Your signature will <strong>only</strong> be used for this specific seminar to prove your attendance. It will appear on official registration sheets and attendance records for this seminar only. Your signature will not be used for any other purposes, shared with third parties, or used for marketing or promotional activities without your explicit consent.
            </p>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 bg-gray-50 flex justify-end">
            <button type="button" onclick="closePrivacyPolicy()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    window.openPrivacyPolicy = window.openPrivacyPolicy || function() { var m = document.getElementById('privacyModal'); if (m) m.classList.remove('hidden'); };
    window.closePrivacyPolicy = window.closePrivacyPolicy || function() { var m = document.getElementById('privacyModal'); if (m) m.classList.add('hidden'); };
    document.addEventListener('DOMContentLoaded', function() {
        var m = document.getElementById('privacyModal');
        if (m) m.addEventListener('click', function(e) { if (e.target === this) closePrivacyPolicy(); });
    });
</script>
