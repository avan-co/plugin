#!/usr/bin/env python3
"""Generate fa_IR.po files for manager module plugins."""

from __future__ import annotations

import polib
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

MODULE_STRINGS = {
    "platform-tasks": {
        "Platform Tasks requires Platform Core to be installed and activated.": "Platform Tasks نیاز به نصب و فعال‌سازی هسته پلتفرم دارد.",
        "Platform Tasks requires Platform Core %s or newer.": "Platform Tasks به هسته پلتفرم %s یا جدیدتر نیاز دارد.",
        "Track team tasks, pending approvals, and manager workload.": "پیگیری وظایف تیم، تأییدهای در انتظار و بار کار مدیر.",
        "Tasks": "وظایف",
        "View tasks": "مشاهده وظایف",
        "Manage tasks": "مدیریت وظایف",
        "Review and manage team tasks and approvals.": "بازبینی و مدیریت وظایف و تأییدهای تیم.",
        "Pending work and task approvals for your team.": "کار در انتظار و تأیید وظایف برای تیم شما.",
        "Open Tasks": "وظایف باز",
        "%d task awaiting review": "%d وظیفه در انتظار بازبینی",
        "%d tasks awaiting review": "%d وظیفه در انتظار بازبینی",
        "Open the task board to approve or assign work.": "تابلو وظایف را برای تأیید یا تخصیص کار باز کنید.",
        "Review tasks": "بازبینی وظایف",
        "Review onboarding checklist": "بازبینی چک‌لیست ورود",
        "Verify new member access and profile completion.": "دسترسی عضو جدید و تکمیل پروفایل را بررسی کنید.",
        "Approve quarterly report draft": "تأیید پیش‌نویس گزارش فصلی",
        "Confirm metrics before publishing to stakeholders.": "شاخص‌ها را قبل از انتشار برای ذینفعان تأیید کنید.",
        "Schedule team sync": "زمان‌بندی جلسه هماهنگی تیم",
        "Coordinate weekly manager review session.": "جلسه بازبینی هفتگی مدیر را هماهنگ کنید.",
        "Pending Tasks": "وظایف در انتظار",
        "Total Tasks": "مجموع وظایف",
        "No tasks yet. Tasks created for your scope will appear here.": "هنوز وظیفه‌ای نیست. وظایف ایجاد‌شده برای محدوده شما اینجا نمایش داده می‌شوند.",
        "Task": "وظیفه",
        "Status": "وضعیت",
        "Updated": "به‌روزرسانی",
        "Track pending approvals and team workload.": "پیگیری تأییدهای در انتظار و بار کار تیم.",
    },
    "platform-team": {
        "Platform Team requires Platform Core to be installed and activated.": "Platform Team نیاز به نصب و فعال‌سازی هسته پلتفرم دارد.",
        "Platform Team requires Platform Core %s or newer.": "Platform Team به هسته پلتفرم %s یا جدیدتر نیاز دارد.",
        "Manage team membership and member visibility for managers.": "مدیریت عضویت تیم و دید مدیران به اعضا.",
        "Team": "تیم",
        "View team members": "مشاهده اعضای تیم",
        "Manage team members": "مدیریت اعضای تیم",
        "View members assigned to your manager scope.": "اعضای تخصیص‌یافته به محدوده مدیریت خود را ببینید.",
        "Members and groups under your oversight.": "اعضا و گروه‌ها تحت نظارت شما.",
        "Team Members": "اعضای تیم",
        "Operations": "عملیات",
        "No team members are assigned to your scope yet.": "هنوز عضوی به محدوده شما تخصیص نشده.",
        "Member": "عضو",
        "Email": "ایمیل",
        "Default": "پیش‌فرض",
        "People and groups assigned to your manager scope.": "افراد و گروه‌های تخصیص‌یافته به محدوده مدیر.",
    },
    "platform-reports": {
        "Platform Reports requires Platform Core to be installed and activated.": "Platform Reports نیاز به نصب و فعال‌سازی هسته پلتفرم دارد.",
        "Platform Reports requires Platform Core %s or newer.": "Platform Reports به هسته پلتفرم %s یا جدیدتر نیاز دارد.",
        "Operational reports for managers across tasks and team modules.": "گزارش‌های عملیاتی برای مدیران از ماژول‌های وظایف و تیم.",
        "Reports": "گزارش‌ها",
        "View manager reports": "مشاهده گزارش‌های مدیر",
        "Operational summaries for your manager workspace.": "خلاصه‌های عملیاتی برای فضای کار مدیر.",
        "Task and team metrics in one place.": "شاخص‌های وظایف و تیم در یک جا.",
        "Tasks Completed": "وظایف تکمیل‌شده",
        "Tasks In Progress": "وظایف در حال انجام",
        "Operational summary": "خلاصه عملیاتی",
        "This report aggregates live data from installed manager modules.": "این گزارش داده زنده از ماژول‌های نصب‌شده مدیر را جمع می‌کند.",
        "Connected modules": "ماژول‌های متصل",
        "Install Tasks and Team modules to populate this report.": "ماژول‌های Tasks و Team را نصب کنید تا این گزارش پر شود.",
        "Total tracked tasks": "مجموع وظایف ردیابی‌شده",
        "Open Tasks": "باز کردن وظایف",
        "Open Team": "باز کردن تیم",
        "Cross-module metrics for manager oversight.": "شاخص‌های بین‌ماژولی برای نظارت مدیر.",
    },
}


def build_po(plugin_slug: str, strings: dict[str, str]) -> None:
    po_path = ROOT / plugin_slug / "languages" / f"{plugin_slug}-fa_IR.po"
    po_path.parent.mkdir(parents=True, exist_ok=True)

    po = polib.POFile()
    po.metadata = {
        "Content-Type": "text/plain; charset=UTF-8",
        "Language": "fa_IR",
    }

    for msgid, msgstr in sorted(strings.items()):
        entry = polib.POEntry(msgid=msgid, msgstr=msgstr)
        po.append(entry)

    po.save(str(po_path))
    po.save_as_mofile(str(po_path.with_suffix(".mo")))
    print(f"Wrote {po_path} ({len(strings)} strings)")


def main() -> None:
    for slug, strings in MODULE_STRINGS.items():
        build_po(slug, strings)


if __name__ == "__main__":
    main()
