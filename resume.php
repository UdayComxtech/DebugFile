<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Parser</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; background-color: #f4f4f4; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); display: inline-block; }
        h2 { color: #333; }
        input {     
            margin-bottom: 10px;
            outline-style: none;
            background: lightgrey;
            padding: 5px;
            border: 1px dashed blue;
            width: 500px;
         }
        #output { margin-top: 20px; text-align: left; padding: 10px; background: #fff; border-radius: 5px; box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.1); }
        table { width: auto; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        fieldset{
            width: fit-content;
            text-align: center;
        }
        h3 { margin-top: 20px; color: #444; }
        pre { background: #eef; padding: 10px; border-radius: 5px; white-space: pre-wrap; word-wrap: break-word; max-height: 300px; overflow-y: auto; }
        
        ::-webkit-scrollbar {
        width: 2px; 
        background: transparent; 
        }

        ::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.2); 
        border-radius: 25px; 
        }

        ::-webkit-scrollbar-thumb:hover {
        background: rgba(0, 0, 0, 0.4);
        }

    </style>
</head>
<body>
    <div class="container">
        <center><fieldset>
            <legend><h2>Upload Resume (PDF)</h2></legend>
            <input type="file" id="fileInput" accept="application/pdf">
        </fieldset></center>
        <div id="output"></div>
    </div>
    
    <script>
        document.getElementById('fileInput').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                document.getElementById('output').innerHTML = `<p><strong>Uploaded File:</strong> ${file.name}</p>`;
                const reader = new FileReader();
                reader.onload = function() {
                    extractText(reader.result, file.name);
                };
                reader.readAsArrayBuffer(file);
            }
        });
        
        async function extractText(pdfData, fileName) {
            const pdf = await pdfjsLib.getDocument({ data: pdfData }).promise;
            let extractedText = "";
            for (let i = 1; i <= pdf.numPages; i++) {
                const page = await pdf.getPage(i);
                const textContent = await page.getTextContent();
                textContent.items.forEach(item => extractedText += item.str + " ");
            }
            parseResumeData(extractedText, fileName);
        }
        
        function parseResumeData(text, fileName) {
            const nameMatch = text.match(/(Name|Full Name):?\s*([A-Z][a-z]+\s[A-Z][a-z]+)/);
            let emailMatch = text.match(/\S+@\S+\.\S+/);
            if (!emailMatch) {
                emailMatch = text.match(/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/);
            }
            let contactMatch = text.match(/(Contact|Phone|Mobile):?\s*(\+?\d[\d\s-]+)/i);
            
            if (!contactMatch) {
                contactMatch = text.match(/\b\+?\d{1,4}?[-.\s]?\(?\d{2,5}?\)?[-.\s]?\d{3,4}[-.\s]?\d{4}\b/);
            }
            
            const educationMatch = text.match(/(Education|Academy):?\s*(.*)/i);
            const certificatesMatch = text.match(/(Certificates|Certifications):?\s*(.*)/i);
            const projectsMatch = text.match(/(Projects|Work Experience):?\s*(.*)/i);
            const experienceMatch = text.match(/(Experience|Work History):?\s*(.*)/i);
            const languagesMatch = text.match(/(Languages Known|Languages):?\s*(.*)/i);
            const internshipMatch = text.match(/(Internship|Training):?\s*(.*)/i);
            
            let skillsMatch = text.match(/Skills:?\s*(.*)/i);
            if (!skillsMatch) {
                skillsMatch = text.match(/Technical Skills:?\s*(.*)/i);
            }
            if (!skillsMatch) {
                skillsMatch = text.match(/Expertise:?\s*(.*)/i);
            }
            
            let name = nameMatch ? nameMatch[2] : "Not Found";
            
            if (name === "Not Found") {
                const fileNameWithoutExt = fileName.replace(/\.pdf$/i, "").replace(/[-_]/g, ' ');
                const fileNameMatch = fileNameWithoutExt.match(/^(.*?)(?:\sresume)?$/i);
                if (fileNameMatch) {
                    name = fileNameMatch[1].trim();
                }
            }
            
            const email = emailMatch ? emailMatch[0] : "Not Found";
            const contact = contactMatch ? contactMatch[0] : "Not Found";
            const education = educationMatch ? educationMatch[2] : "Not Found";
            const certificates = certificatesMatch ? certificatesMatch[2] : "Not Found";
            const projects = projectsMatch ? projectsMatch[2] : "Not Found";
            const experience = experienceMatch ? experienceMatch[2] : "Not Found";
            const languages = languagesMatch ? languagesMatch[2] : "Not Found";
            const internship = internshipMatch ? internshipMatch[2] : "Not Found";
            const skills = skillsMatch ? skillsMatch[1] : "Not Found";
            
            document.getElementById('output').innerHTML += `
                <table>
                    <tr><th>Field</th><th>Value</th></tr>
                    <tr><td>Name</td><td>${name}</td></tr>
                    <tr><td>Email</td><td>${email}</td></tr>
                    <tr><td>Contact</td><td>${contact}</td></tr>
                    <tr><td>Education</td><td>${education}</td></tr>
                    <tr><td>Certificates</td><td>${certificates}</td></tr>
                    <tr><td>Projects</td><td>${projects}</td></tr>
                    <tr><td>Experience</td><td>${experience}</td></tr>
                    <tr><td>Languages</td><td>${languages}</td></tr>
                    <tr><td>Internship</td><td>${internship}</td></tr>
                    <tr><td>Skills</td><td>${skills}</td></tr>
                </table>
                <h3>Extracted Resume Content:</h3>
                <pre>${text}</pre>
            `;
        }
    </script>
</body>
</html>
