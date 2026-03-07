<?php

/**
 * Search Form Component
 * Usage: <?= $this->include('frontend/components/search-form', ['action' => 'search', 'filters' => $filters]) ?>
 */
?>
<div class="card <?= esc($cardClass ?? 'shadow-sm border-0 mb-4') ?>">
    <div class="card-body <?= esc($bodyClass ?? 'p-4') ?>">
        <h5 class="card-title mb-3">
            <i class="fas <?= esc($icon ?? 'fa-filter') ?> me-2"></i>
            <?= esc($title ?? 'Filter & Pencarian') ?>
        </h5>

        <form action="<?= base_url($action ?? 'search') ?>" method="get">
            <div class="row g-3">
                <?php if (!empty($filters) && is_array($filters)): ?>
                    <?php foreach ($filters as $filter): ?>
                        <div class="<?= esc($filter['colClass'] ?? 'col-md-4') ?>">
                            <?php if (!empty($filter['label'])): ?>
                                <label for="<?= esc($filter['name']) ?>" class="form-label"><?= esc($filter['label']) ?></label>
                            <?php endif; ?>

                            <?php if ($filter['type'] === 'select'): ?>
                                <select class="form-select" id="<?= esc($filter['name']) ?>" name="<?= esc($filter['name']) ?>">
                                    <option value=""><?= esc($filter['placeholder'] ?? 'Semua') ?></option>
                                    <?php if (!empty($filter['options']) && is_array($filter['options'])): ?>
                                        <?php foreach ($filter['options'] as $option): ?>
                                            <option value="<?= esc($option['value']) ?>"
                                                <?= (isset($currentFilters[$filter['name']]) && $currentFilters[$filter['name']] == $option['value']) ? 'selected' : '' ?>>
                                                <?= esc($option['label']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>

                            <?php elseif ($filter['type'] === 'text'): ?>
                                <input type="text"
                                    class="form-control"
                                    id="<?= esc($filter['name']) ?>"
                                    name="<?= esc($filter['name']) ?>"
                                    placeholder="<?= esc($filter['placeholder'] ?? '') ?>"
                                    value="<?= esc($currentFilters[$filter['name']] ?? '') ?>">

                            <?php elseif ($filter['type'] === 'search'): ?>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control"
                                        id="<?= esc($filter['name']) ?>"
                                        name="<?= esc($filter['name']) ?>"
                                        placeholder="<?= esc($filter['placeholder'] ?? 'Cari...') ?>"
                                        value="<?= esc($currentFilters[$filter['name']] ?? '') ?>">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search me-1"></i>
                                        <?= esc($submitText ?? 'Cari') ?>
                                    </button>
                                </div>

                            <?php elseif ($filter['type'] === 'date'): ?>
                                <input type="date"
                                    class="form-control"
                                    id="<?= esc($filter['name']) ?>"
                                    name="<?= esc($filter['name']) ?>"
                                    value="<?= esc($currentFilters[$filter['name']] ?? '') ?>">

                            <?php elseif ($filter['type'] === 'hidden'): ?>
                                <input type="hidden"
                                    name="<?= esc($filter['name']) ?>"
                                    value="<?= esc($filter['value'] ?? '') ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Default Search Input if no filters provided -->
                <?php if (empty($filters)): ?>
                    <div class="col-md-8">
                        <div class="input-group">
                            <input type="text"
                                class="form-control"
                                name="q"
                                placeholder="<?= esc($placeholder ?? 'Masukkan kata kunci pencarian...') ?>"
                                value="<?= esc($currentFilters['q'] ?? '') ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search me-1"></i> <?= esc($submitText ?? 'Cari') ?>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Submit Button (if not search type) -->
                <?php if (!empty($filters) && !in_array('search', array_column($filters, 'type'))): ?>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> <?= esc($submitText ?? 'Cari') ?>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Reset Button -->
                <?php if (!empty($showReset) && $showReset): ?>
                    <div class="col-md-auto">
                        <a href="<?= base_url($action ?? 'search') ?>" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-undo me-1"></i> Reset
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>