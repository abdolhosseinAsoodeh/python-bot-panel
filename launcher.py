import os
import sys
import subprocess
import signal

# Python Bot Launcher
# Usage: python launcher.py <project_dir> <script_path> <log_path> <pid_path> [python_exe]

if len(sys.argv) < 5:
    print("Usage: launcher.py <project_dir> <script_path> <log_path> <pid_path> [python_exe]")
    sys.exit(1)

project_dir = os.path.abspath(sys.argv[1])
script_path = os.path.abspath(sys.argv[2])
log_path = os.path.abspath(sys.argv[3])
pid_path = os.path.abspath(sys.argv[4])
python_exe = sys.argv[5] if len(sys.argv) > 5 else sys.executable

# Ensure directories exist
os.makedirs(os.path.dirname(log_path), exist_ok=True)
os.makedirs(os.path.dirname(pid_path), exist_ok=True)

with open(log_path, "a", encoding="utf-8", buffering=1) as log:
    log.write(f"\n=== Bot starting at {os.environ.get('START_TIME', '')} ===\n")
    log.write(f"Project: {project_dir}\nScript: {script_path}\nInterpreter: {python_exe}\n")

    popen_kwargs = {
        "cwd": project_dir,
        "stdout": log,
        "stderr": subprocess.STDOUT,
    }

    if sys.platform == "win32":
        popen_kwargs["creationflags"] = subprocess.CREATE_NEW_PROCESS_GROUP
    else:
        popen_kwargs["preexec_fn"] = os.setsid

    try:
        process = subprocess.Popen([python_exe, "-u", script_path], **popen_kwargs)
        with open(pid_path, "w", encoding="utf-8") as pid_file:
            pid_file.write(str(process.pid))

        log.write(f"PID: {process.pid}\n=== Bot running ===\n")
        exit_code = process.wait()
        log.write(f"\n=== Bot stopped, exit code: {exit_code} ===\n")
    except Exception as e:
        log.write(f"\n[Launcher Error]: {e}\n")
    finally:
        if os.path.exists(pid_path):
            try:
                os.remove(pid_path)
            except OSError:
                pass
