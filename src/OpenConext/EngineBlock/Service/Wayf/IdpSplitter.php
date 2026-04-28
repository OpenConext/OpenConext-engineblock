<?php

/**
 * Copyright 2026 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlock\Service\Wayf;

final class IdpSplitter
{
    /**
     * Splits the full IdP list into preferred (connected, in configured order) and regular (everything else).
     * Preferred IdPs that are not connected are excluded from both sections.
     *
     * @param WayfIdp[] $idpList           Full transformed IdP list
     * @param array     $preferredEntityIds Ordered list of entity IDs to feature at the top
     */
    public function split(array $idpList, array $preferredEntityIds): WayfSplitResult
    {
        if (empty($preferredEntityIds)) {
            return new WayfSplitResult(preferred: [], regular: $idpList);
        }

        $orderMap = array_flip($preferredEntityIds);
        $preferredBuckets = array_fill(0, count($preferredEntityIds), []);
        $regular = [];

        foreach ($idpList as $idp) {
            $entityId = $idp->entityId;
            if (isset($orderMap[$entityId])) {
                if ($idp->accessible) {
                    $preferredBuckets[$orderMap[$entityId]][] = $idp;
                }
                // Unconnected preferred IdPs are excluded from both sections.
            } else {
                $regular[] = $idp;
            }
        }

        $mergeArgs = array_values(array_filter($preferredBuckets));
        $preferred = empty($mergeArgs) ? [] : array_merge(...$mergeArgs);

        return new WayfSplitResult(preferred: $preferred, regular: $regular);
    }
}
