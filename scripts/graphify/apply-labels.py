"""Apply curated community names to the Dragon Glow graphify graph.

`graphify cluster-only --no-label` writes "Community N" placeholders, and the
graphify-side `.graphify_labels.json` is keyed by raw community id, so every
re-cluster renames communities. This script re-attaches the curated names in
`curated-labels.json` by looking up the nodes that anchor each name instead of
trusting a cid, then writes the labels sidecar + membership signature that
`graphify cluster-only` reads back.

Usage (from the repo root, after any graph rebuild):

    graphify cluster-only . --no-label      # optional: refresh the clustering
    python scripts/graphify/apply-labels.py
    graphify cluster-only . --no-label      # regenerate report + html with names

Run it with the interpreter that has graphify installed, e.g.
C:\\Users\\<you>\\AppData\\Roaming\\uv\\tools\\graphifyy\\Scripts\\python.exe
"""

import json
from collections import Counter, defaultdict
from pathlib import Path

from graphify.build import build_from_json
from graphify.cluster import community_member_sigs, label_communities_by_hub

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "graphify-out"
CURATED = Path(__file__).with_name("curated-labels.json")


def main() -> int:
    graph_path = OUT / "graph.json"
    if not graph_path.exists():
        raise SystemExit(f"no graph at {graph_path} — run `graphify extract . --code-only` first")

    raw = json.loads(graph_path.read_text(encoding="utf-8"))
    G = build_from_json(raw, directed=bool(raw.get("directed", False)))

    communities: dict[int, list[str]] = defaultdict(list)
    node_community: dict[str, int] = {}
    for node in raw["nodes"]:
        cid = node.get("community")
        if cid is None:
            continue
        communities[int(cid)].append(node["id"])
        node_community[node["id"]] = int(cid)

    names: dict[str, list[str]] = json.loads(CURATED.read_text(encoding="utf-8"))["labels"]
    votes: dict[int, list[str]] = defaultdict(list)
    unmatched: list[str] = []
    for name, anchors in names.items():
        hits = Counter(node_community[a] for a in anchors if a in node_community)
        if not hits:
            unmatched.append(name)
            continue
        votes[hits.most_common(1)[0][0]].append(name)

    labels = label_communities_by_hub(G, dict(communities))
    applied = 0
    for cid, candidates in votes.items():
        winner = Counter(candidates).most_common(1)[0][0]
        labels[cid] = winner
        applied += 1

    (OUT / ".graphify_labels.json").write_text(
        json.dumps({str(k): v for k, v in labels.items()}, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )
    (OUT / ".graphify_labels.json.sig").write_text(
        json.dumps({str(k): v for k, v in community_member_sigs(dict(communities)).items()}),
        encoding="utf-8",
    )

    print(f"applied {applied}/{len(names)} curated names across {len(communities)} communities")
    if unmatched:
        print("anchors gone from the graph (name dropped, hub label used):")
        for name in unmatched:
            print(f"  - {name}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
