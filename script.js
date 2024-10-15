// Add animation to the terminal output
const terminalOutput = document.querySelector('.terminal-output');

terminalOutput.addEventListener('DOMSubtreeModified', () => {
    const terminalOutputText = terminalOutput.innerHTML;
    const lines = terminalOutputText.split('\n');
    const lastLine = lines[lines.length - 1];

    if (lastLine.includes('Response Code:')) {
        const responseCode = lastLine.split(': ')[1];
        const animateText = `Response Code: <span style="color: #${responseCode >= 200 && responseCode < 300 ? '34C759' : responseCode >= 400 && responseCode < 500 ? 'FF3B47' : 'F7DC6F'}">${responseCode}</span>`;

        terminalOutput.innerHTML = terminalOutputText.replace(lastLine, animateText);
    }
});