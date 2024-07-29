<script setup lang="ts">

</script>

<template>
    <div
        class="flex flex-col rounded-md border border-zinc-200 bg-white dark:border-surface-700 dark:text-white/80 dark:bg-surface-900">
        <div class="flex items-center justify-between px-2 py-1">
            <div class="text-sm font-bold text-zinc-700 dark:text-zinc-300">ТЕСТ-409</div>
            <div><button
                    class="flex h-8 w-8 items-center justify-center rounded-full transition-colors duration-100 hover:bg-zinc-100 hover:text-zinc-900 dark:hover:bg-zinc-700 dark:hover:text-zinc-50 text-color"><i
                        class="pi pi-ellipsis-v" style="font-size: 1rem"></i></button></div>
        </div>
        <div class="shrink-0 border-t border-zinc-200 dark:border-surface-700">
            <table class="w-full">
                <tbody>
                    <tr class="border-b border-zinc-200 text-zinc-900 dark:border-surface-700 dark:text-zinc-200">
                        <th
                            class="w-7 border-r border-zinc-200 px-1 py-0.5 text-sm font-semibold dark:border-surface-700">
                            №
                        </th>
                        <th
                            class="border-r border-zinc-200 px-1 py-0.5 text-left text-sm font-semibold dark:border-surface-700">
                            Предмет</th>
                        <th title="Кабинет" class="w-1/6 px-1 py-0.5 text-left text-sm font-semibold">Каб</th>
                    </tr>
                    <tr class="border-b border-zinc-200 last:border-b-0 dark:border-surface-700">
                        <td
                            class="border-r border-zinc-200 px-1 py-0.5 text-center text-sm text-color dark:border-surface-700">
                            0</td>
                        <td class="border-r border-zinc-200 text-zinc-700 dark:border-surface-700 dark:text-zinc-300">
                            <div><input class="w-full bg-transparent px-1 py-0.5 text-sm" hidden="" type="text" value=""
                                    name="subject"><button
                                    class="w-full px-1 py-0.5 text-left text-sm">-------------</button></div>
                        </td>
                        <td class="text-sm"><input
                                class="w-full bg-transparent px-1 py-0.5 text-sm text-zinc-700 dark:text-zinc-300"
                                type="text" value="" name="cabinet"></td>
                    </tr>
                    <tr class="border-b border-zinc-200 last:border-b-0 dark:border-surface-700">
                        <td
                            class="border-r border-zinc-200 px-1 py-0.5 text-center text-sm text-color dark:border-surface-700">
                            1</td>
                        <td class="border-r border-zinc-200 text-zinc-700 dark:border-surface-700 dark:text-zinc-300">
                            <div><input class="w-full bg-transparent px-1 py-0.5 text-sm" hidden="" type="text" value=""
                                    name="subject"><button
                                    class="w-full px-1 py-0.5 text-left text-sm">-------------</button></div>
                        </td>
                        <td class="text-sm"><input
                                class="w-full bg-transparent px-1 py-0.5 text-sm text-zinc-700 dark:text-zinc-300"
                                type="text" value="" name="cabinet"></td>
                    </tr>
                    <tr class="border-b border-zinc-200 last:border-b-0 dark:border-surface-700">
                        <td
                            class="border-r border-zinc-200 px-1 py-0.5 text-center text-sm text-color dark:border-surface-700">
                            2</td>
                        <td class="border-r border-zinc-200 text-zinc-700 dark:border-surface-700 dark:text-zinc-300">
                            <div><input class="w-full bg-transparent px-1 py-0.5 text-sm" hidden="" type="text" value=""
                                    name="subject"><button
                                    class="w-full px-1 py-0.5 text-left text-sm">-------------</button></div>
                        </td>
                        <td class="text-sm"><input
                                class="w-full bg-transparent px-1 py-0.5 text-sm text-zinc-700 dark:text-zinc-300"
                                type="text" value="" name="cabinet"></td>
                    </tr>
                    <tr class="border-b border-zinc-200 last:border-b-0 dark:border-surface-700">
                        <td
                            class="border-r border-zinc-200 px-1 py-0.5 text-center text-sm text-color dark:border-surface-700">
                            3</td>
                        <td class="border-r border-zinc-200 text-zinc-700 dark:border-surface-700 dark:text-zinc-300">
                            <div><input class="w-full bg-transparent px-1 py-0.5 text-sm" hidden="" type="text" value=""
                                    name="subject"><button
                                    class="w-full px-1 py-0.5 text-left text-sm">-------------</button></div>
                        </td>
                        <td class="text-sm"><input
                                class="w-full bg-transparent px-1 py-0.5 text-sm text-zinc-700 dark:text-zinc-300"
                                type="text" value="" name="cabinet"></td>
                    </tr>
                    <tr class="border-b border-zinc-200 last:border-b-0 dark:border-surface-700">
                        <td
                            class="border-r border-zinc-200 px-1 py-0.5 text-center text-sm text-color dark:border-surface-700">
                            4</td>
                        <td class="border-r border-zinc-200 text-zinc-700 dark:border-surface-700 dark:text-zinc-300">
                            <div><input class="w-full bg-transparent px-1 py-0.5 text-sm" hidden="" type="text" value=""
                                    name="subject"><button
                                    class="w-full px-1 py-0.5 text-left text-sm">-------------</button></div>
                        </td>
                        <td class="text-sm"><input
                                class="w-full bg-transparent px-1 py-0.5 text-sm text-zinc-700 dark:text-zinc-300"
                                type="text" value="" name="cabinet"></td>
                    </tr>
                    <tr class="border-b border-zinc-200 last:border-b-0 dark:border-surface-700">
                        <td
                            class="border-r border-zinc-200 px-1 py-0.5 text-center text-sm text-color dark:border-surface-700">
                            5</td>
                        <td class="border-r border-zinc-200 text-zinc-700 dark:border-surface-700 dark:text-zinc-300">
                            <div><input class="w-full bg-transparent px-1 py-0.5 text-sm" hidden="" type="text" value=""
                                    name="subject"><button
                                    class="w-full px-1 py-0.5 text-left text-sm">-------------</button></div>
                        </td>
                        <td class="text-sm"><input
                                class="w-full bg-transparent px-1 py-0.5 text-sm text-zinc-700 dark:text-zinc-300"
                                type="text" value="" name="cabinet"></td>
                    </tr>
                    <tr class="border-b border-zinc-200 last:border-b-0 dark:border-surface-700">
                        <td
                            class="border-r border-zinc-200 px-1 py-0.5 text-center text-sm text-color dark:border-surface-700">
                            6</td>
                        <td class="border-r border-zinc-200 text-zinc-700 dark:border-surface-700 dark:text-zinc-300">
                            <div><input class="w-full bg-transparent px-1 py-0.5 text-sm" hidden="" type="text" value=""
                                    name="subject"><button
                                    class="w-full px-1 py-0.5 text-left text-sm">-------------</button></div>
                        </td>
                        <td class="text-sm"><input
                                class="w-full bg-transparent px-1 py-0.5 text-sm text-zinc-700 dark:text-zinc-300"
                                type="text" value="" name="cabinet"></td>
                    </tr>
                    <tr class="border-b border-zinc-200 last:border-b-0 dark:border-surface-700">
                        <td
                            class="border-r border-zinc-200 px-1 py-0.5 text-center text-sm text-color dark:border-surface-700">
                            7</td>
                        <td class="border-r border-zinc-200 text-zinc-700 dark:border-surface-700 dark:text-zinc-300">
                            <div><input class="w-full bg-transparent px-1 py-0.5 text-sm" hidden="" type="text" value=""
                                    name="subject"><button
                                    class="w-full px-1 py-0.5 text-left text-sm">__________</button></div>
                        </td>
                        <td class="text-sm"><input
                                class="w-full bg-transparent px-1 py-0.5 text-sm text-zinc-700 dark:text-zinc-300"
                                type="text" value="" name="cabinet"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</template>